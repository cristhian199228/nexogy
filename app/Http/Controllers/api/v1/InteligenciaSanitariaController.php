<?php

namespace App\Http\Controllers\api\v1;

use App\FichaPaciente;
use App\Http\Controllers\Controller;
use App\Jobs\SendMailInteligenciaSanitariaJob;
use App\Mail\MailInteligenciaSanitaria;
use App\PcrPruebaMolecular;
use App\PcrReevaluacion;
use App\PrsReevaluacion;
use App\Service\FiltroService;
use App\Service\PruebaSerologicaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Monolog\Handler\IFTTTHandler;

class InteligenciaSanitariaController extends Controller
{
    //
    public function getData(Request $request) {

        $collection = FichaPaciente::whereBetween(DB::raw('date(created_at)'), [$request->fecha_inicio, $request->fecha_fin])
            ->whereHas('PacienteIsos', function (Builder $q) use ($request){
                $q->search($request->buscar);
            })
            ->with('PacienteIsos.Empresa','AntecedentesEp','DatosClinicos','Estacion.Sede');

        $filtro = new FiltroService($collection);

        if ($request->has('empresa')) {
            $filtro->empresa($request->empresa);
        }
        if ($request->has('ps')) {
            $filtro->pruebaSerologica($request->ps);
        }

        if ($request->has('resultado')) {
            $filtro->pruebaMolecular($request->resultado);
        }

        if ($request->has('sede')) {
            $filtro->sede($request->sede);
        }

        $collection->with(['PcrPruebaMolecular' => function($q) {
            $q->whereNotNull('resultado')
                ->with('PcrFotoMuestra')
                ->with(['reevaluacion' => function ($q) {
                    $q->latest();
                }]);
        }])->with(["PruebaSerologica" => function ($q) {
            $q->select('idpruebaserologicas','idfichapacientes','p1_positivo_recuperado','p1_react1gm','p1_positivo_persistente',
                'p1_reactigg','p1_reactigm_igg','no_reactivo','created_at','p1_positivo_vacunado')
                ->where("invalido", 0)
                ->whereNotNull("no_reactivo")
                ->with(['reevaluacion' => function ($q) {
                    $q->latest();
                }])
                ->latest()->get();
        }]);

        $fichas = $collection->oldest()->paginate(15);

        $pagina = $fichas->currentPage();
        $total = $fichas->total();
        $contador = $total - (15*($pagina-1));

        foreach ($fichas as $ficha) {
            foreach ($ficha->PruebaSerologica as $ps) {
                try {
                    $service = new PruebaSerologicaService($ps);
                    $ps->resultado = $service->result();
                    $ps->dias_bloqueo = $service->diasBloqueo();
                } catch (\Exception $exception){
                    //
                }
            }
            $ficha->fecha = $ficha->created_at->format('d/m/Y');
            $ficha->contador = $contador;
            --$contador;
        }

        return $fichas;
    }

    public function mostrarXml(Request $request) {

        $output = $this->generarXml($request->idficha_paciente);

        return response($output)->withHeaders([
            'Content-Type' => 'text/xml'
        ]);
    }

    public function guardarReevaluacionPcr(Request $request) {
        $validated = $request->validate([
            'idpcr_prueba_molecular' => 'required',
            'dias_bloqueo' => 'required'
        ]);
        $reevaluacion = new PcrReevaluacion();
        $reevaluacion->idpcr_prueba_molecular = $validated['idpcr_prueba_molecular'];
        $reevaluacion->dias_bloqueo = $validated['dias_bloqueo'];
        $reevaluacion->id_usuario = Auth::id();
        $reevaluacion->save();

        return response([
            'message' => 'Reevaluación guardada correctamente',
            'data' => $reevaluacion,
        ]);
    }

    public function guardarReevaluacionPrs(Request $request) {
        $validated = $request->validate([
            'idpruebaserologicas' => 'required',
            'dias_bloqueo' => 'required'
        ]);
        $reevaluacion = new PrsReevaluacion();
        $reevaluacion->idpruebaserologicas = $validated['idpruebaserologicas'];
        $reevaluacion->dias_bloqueo = $validated['dias_bloqueo'];
        $reevaluacion->id_usuario = Auth::id();
        $reevaluacion->save();

        return response([
            'message' => 'Reevaluación guardada correctamente',
            'data' => $reevaluacion,
        ]);
    }

    public function enviarCorreo(Request $request) {

        $idFicha = $request->idficha_paciente;
        Storage::put("xml/$idFicha.xml", $this->generarXml($idFicha));
        SendMailInteligenciaSanitariaJob::dispatch(FichaPaciente::findOrFail($idFicha), Auth::user()->email);

        return response([
            'message' => 'Correo enviado correctamente'
        ]);
    }

    public function generarXml($idFicha) {

        $ficha = FichaPaciente::where('idficha_paciente', $idFicha)
            ->with('PacienteIsos','AntecedentesEp','DatosClinicos')
            ->with(['PcrPruebaMolecular' => function($q) {
                $q->whereNotNull('resultado')
                    ->with('PcrFotoMuestra')
                    ->with(['reevaluacion' => function ($q) {
                        $q->latest();
                    }]);
            }])->with(["PruebaSerologica" => function ($q) {
                $q->select('idpruebaserologicas','idfichapacientes','p1_positivo_recuperado','p1_react1gm','p1_positivo_persistente',
                    'p1_reactigg','p1_reactigm_igg','no_reactivo','created_at','p1_positivo_vacunado')
                    ->where("invalido", 0)
                    ->whereNotNull("no_reactivo")
                    ->with(['reevaluacion' => function ($q) {
                        $q->latest();
                    }])
                    ->latest()->get();
            }])
            ->firstOrFail();

        foreach ($ficha->PruebaSerologica as $ps) {
            try {
                $service = new PruebaSerologicaService($ps);
                $ps->resultado = $service->cvResult();
                $ps->dias_bloqueo = $service->diasBloqueo();
            } catch (\Exception $exception){
                //
            }
        }

        $ficha->fecha_hora = $ficha->created_at->format('Ymd His');
        $ficha->proceso = $ficha->turno === 2 ? 14 : 12;

        return View::make('xml.xml_pcr_reevaluacion')->with('ficha', $ficha)->render();
    }

}
