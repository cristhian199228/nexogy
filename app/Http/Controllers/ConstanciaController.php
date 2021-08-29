<?php

namespace App\Http\Controllers;

use App\ConsultaWhatsAppPcr;
use App\DescargasConstancia;
use App\EmpleadoNs;
use App\EnvioWP;
use App\EnvioWpPcr;
use App\ExcelNicole;
use App\Exports\ExportExcelPcrMina;
use App\Exports\ExportReporteWhastApp;
use App\Exports\NuevoResponceCenterPcrExport;
use App\Http\Controllers\api\v1\EstacionController;
use App\Instancia;
use App\Mail\EnvioXMLPcrNegativos;
use App\Mail\ResponceCenterPcrMail;
use App\MensajeWhatsApp;
use App\PacienteIsos;
use App\PcrConsumoMunoz;
use App\PcrEnvioCorreo;
use App\PcrPruebaMolecular;
use App\Service\EstacionService;
use App\Service\FichaPacienteService;
use App\Service\InstanciaService;
use App\Service\MediwebService;
use App\Service\PacienteService;
use App\Service\PruebaMolecularService;
use App\Service\PruebaSerologicaService;
use App\SolicitarConstancia;
use Barryvdh\DomPDF\PDF;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\FichaPaciente;
use App\PruebaSerologica;
use App\CitasAutomaticasMw;
use App\Paciente;
use DateTime;
use App\Mail\SendMailable;
use App\Mail\EnvioXML;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendEmailJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Spatie\Async\Pool;
use XMLWriter;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Response;
use App\Estacion;
use App\DatosClinicos;
use App\AntecedentesEp;
use App\SolicitudConstancia;
use Illuminate\Support\Facades\Storage;
use SoapClient;
use Illuminate\Mail\Mailable;
use KubAT\PhpSimple\HtmlDomParser;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;


class ConstanciaController extends Controller
{
    public function buscarRegistros(Request $request)
    {
        $request->validate([
            'date' => 'date_format:Y-m-d',
            'document' => 'required'
        ]);

        $paciente_isos = PacienteIsos::where('fecha_nacimiento', $request->date)
            ->where('numero_documento', $request->document)
            ->first();

        if($paciente_isos) {
            $fichas = FichaPaciente::where('id_paciente', $paciente_isos->idpacientes)
                ->with(["PacienteIsos" => function($q) {
                    $q->select('idpacientes','nombres','apellido_paterno','apellido_materno','idempresa','fecha_nacimiento')
                        ->with("Empresa:idempresa,descripcion");
                }])
                ->with("PcrPruebaMolecular")
                ->with(["PruebaSerologica" => function ($q) {
                    $q->select('idpruebaserologicas','idfichapacientes','p1_positivo_recuperado','p1_react1gm','p1_reactigg'
                        ,'p1_reactigm_igg','no_reactivo','created_at','p1_positivo_persistente','p1_positivo_vacunado','invalido')
                        ->where("invalido", 0)
                        ->whereNotNull("no_reactivo")
                        ->latest();
                }])
                ->with(["pruebaAntigena" => function ($q) {
                    $q->select('id','idficha_paciente','resultado','created_at')
                        ->whereIn("resultado", [0, 1])
                        ->latest();
                }])
                ->latest()
                ->get();

            return response([
                'message' => 'Paciente encontrado!',
                'data' => [
                    'pruebas' => FichaPacienteService::mergePruebas($fichas),
                    'paciente' => $paciente_isos
                ]
            ]);
        }

        return response([
            'message' => 'Datos Inválidos'
        ], 401);
    }

    public function descargarConstanciaPrs(Request $request)
    {
        $ficha = FichaPaciente::whereHas("PruebaSerologica", function ($q) use ($request) {
                $q->where('idpruebaserologicas', $request->id);
            })
            ->with('AntecedentesEp','DatosClinicos','PacienteIsos')
            ->with(["PruebaSerologica" => function ($q) {
                $q->select('idpruebaserologicas','idfichapacientes','p1_positivo_recuperado','p1_react1gm','p1_reactigg'
                        ,'p1_reactigm_igg','no_reactivo','created_at','p1_positivo_persistente',
                        'p1_positivo_vacunado')
                    ->where("invalido", 0)
                    ->whereNotNull("no_reactivo")
                    ->latest();
            }])
            ->first();

        if($ficha) {
            $pac = $ficha->PacienteIsos;

            foreach ($ficha->PruebaSerologica as $ps) {
                $ps->resultado = (new PruebaSerologicaService($ps))->result();
            }

            $sintomas_str = "";
            $ant_str = "";

            if (count($ficha->DatosClinicos) > 0) {
                foreach ($ficha->DatosClinicos as $sintoma) {
                    $sintomas_arr = [];
                    if ($sintoma->tos) array_push($sintomas_arr, "tos");
                    if ($sintoma->dolor_garganta) array_push($sintomas_arr, "dolor de garganta");
                    if ($sintoma->dificultad_respiratoria) array_push($sintomas_arr, "dificultad respiratoria");
                    if ($sintoma->fiebre) array_push($sintomas_arr, "fiebre");
                    if ($sintoma->malestar_general) array_push($sintomas_arr, "malestar general");
                    if ($sintoma->diarrea) array_push($sintomas_arr, "diarrea");
                    if ($sintoma->anosmia_ausegia) array_push($sintomas_arr, "anosmia", "ausegia");
                    if ($sintoma->nauseas_vomitos) array_push($sintomas_arr, "náuseas", "vómitos");
                    if ($sintoma->congestion_nasal) array_push($sintomas_arr, "congestión nasal");
                    if ($sintoma->cefalea) array_push($sintomas_arr, "cefálea");
                    if ($sintoma->irritabilidad_confusion) array_push($sintomas_arr, "irritabilidad", "confusión");
                    if ($sintoma->falta_aliento) array_push($sintomas_arr, "falta de aliento");
                    if ($sintoma->otros) array_push($sintomas_arr, "otros: " . $sintoma->otros);
                    if ($sintoma->toma_medicamento) array_push($sintomas_arr, "toma medicamento: " . $sintoma->toma_medicamento);
                    if ($sintoma->fecha_inicio_sintomas) array_push($sintomas_arr, "fecha de inicio de síntomas: " . Carbon::parse($sintoma->fecha_inicio_sintomas)->format('d/m/Y') );
                    $sintomas_str = implode(", ", $sintomas_arr);
                }
            }

            if (count($ficha->AntecedentesEp) > 0) {
                foreach ($ficha->AntecedentesEp as $ant) {
                    $ant_arr = [];
                    if ($ant->contacto_cercano) array_push($ant_arr, "contacto cercano con investigado covid");
                    if ($ant->fecha_ultimo_contacto) array_push($ant_arr, "fecha de último contacto: " . $ant->fecha_ultimo_contacto);
                    if ($ant->conv_covid) array_push($ant_arr, "conversación con investigado covid");
                    if ($ant->dias_viaje) array_push($ant_arr, "ha viajadado en los últimos 14 días");
                    if ($ant->paises_visitados) array_push($ant_arr, "lugares visitados: " . $ant->paises_visitados);
                    if ($ant->fecha_llegada) array_push($ant_arr, "fecha de llegada viaje: " . $ant->fecha_llegada);
                    if ($ant->medio_transporte) array_push($ant_arr, "medio de transporte utilizado: " . $ant->medio_transporte);
                    if ($ant->debilite_sistema) array_push($ant_arr, "condición que debilite sistema: " . $ant->debilite_sistema);
                    $ant_str = implode(", ", $ant_arr);
                }
            }

            $constancia = [
                'id' => $request->id,
                'nom_completo' => $pac->nombres . " " . $pac->apellido_paterno . " " . $pac->apellido_materno,
                'edad' => Carbon::parse($pac->fecha_nacimiento)->age,
                'nro_documento' => $pac->numero_documento,
                'empresa' => $pac->Empresa->descripcion,
                'fecha' => Carbon::parse($ficha->created_at)->format('d/m/Y'),
                'ae' => Str::upper($ant_str),
                'dc' => Str::upper($sintomas_str),
                'res' => $ficha->PruebaSerologica[0]->resultado,
                'url' => COVID_URL . '/descargarConstanciaPRS/'. $request->id,
            ];

            $pdf = \PDF::loadView('pdf.certificado', compact('constancia'));
            return $pdf->stream('certificado_prs'.$request->id.'.pdf');
            //return $ficha;
        } else {
            abort(404);
        }
    }

    public function descargarConstanciaPcr(Request $request) {

        $ficha = FichaPaciente::whereHas("PcrPruebaMolecular", function ($q) use ($request) {
                $q->where('idpcr_pruebas_moleculares', $request->id)
                    ->whereIn("resultado", [0, 1])
                    ->whereHas("PcrEnvioMunoz");
            })
            ->whereHas("PacienteIsos")
            ->with(["PacienteIsos" => function($q) {
                $q->with("Empresa:idempresa,descripcion");
            }])
            ->with('PcrPruebaMolecular.PcrEnvioMunoz')
            ->first();

        if($ficha) {
            $pac = $ficha->PacienteIsos;
            if($pac->sexo == "M") $sexo = "Masculino";
            else $sexo = "Femenino";

            $constancia = [
                'id' => $ficha->idficha_paciente,
                'nom_completo' => $pac->nombres . " " . $pac->apellido_paterno . " " . $pac->apellido_materno,
                'edad' => Carbon::parse($pac->fecha_nacimiento)->age,
                'sexo' => $sexo,
                'tipo_documento' => getStrTipoDocumento($pac->tipo_documento),
                'nro_documento' => $pac->numero_documento,
                'codigo_orden' => $ficha->PcrPruebaMolecular->idpcr_pruebas_moleculares,
                'codigo_muestra' => $ficha->PcrPruebaMolecular->PcrEnvioMunoz->transaction_id,
                'telefono' => $pac->celular,
                'fecha_muestra' => $ficha->PcrPruebaMolecular->created_at->format('d/m/Y'),
                'hora_muestra' => $ficha->PcrPruebaMolecular->created_at->format('H:i'),
                'fecha_resultado' => $ficha->PcrPruebaMolecular->updated_at->format('d/m/Y'),
                'direccion' => Str::title($pac->direccion),
                'res' => getStrPcrResult($ficha->PcrPruebaMolecular->resultado),
                'url' => COVID_URL . '/descargarConstanciaPCR/'. $request->id,
            ];

            $pdf = \PDF::loadView('pdf.certificado_pcr', compact('constancia'));
            return $pdf->stream('certificado_pcr'.$request->id.'.pdf');
        } else {
            abort(404);
        }
    }

    public function descargarConstanciaAG(Request $request)
    {
        $ficha = FichaPaciente::whereHas("pruebaAntigena", function ($q) use ($request) {
                $q->where('id', $request->id);
            })
            ->with(["PacienteIsos" => function ($q) {
                $q->select('idpacientes', 'nombres', 'apellido_paterno', 'apellido_materno', 'idempresa', 'fecha_nacimiento', 'numero_documento')
                    ->with("Empresa:idempresa,descripcion");
            }])
            ->with('DatosClinicos', 'AntecedentesEp')
            ->with(["PruebaAntigena" => function ($q) {
                $q->whereIn("resultado", [0, 1])
                    ->latest();
            }])
            ->first();

        if ($ficha && count($ficha->PruebaAntigena) > 0) {
            $pac = $ficha->PacienteIsos;

            $sintomas_str = "";
            $ant_str = "";

            if (count($ficha->DatosClinicos) > 0) {
                foreach ($ficha->DatosClinicos as $sintoma) {
                    $sintomas_arr = [];
                    if ($sintoma->tos) array_push($sintomas_arr, "tos");
                    if ($sintoma->dolor_garganta) array_push($sintomas_arr, "dolor de garganta");
                    if ($sintoma->dificultad_respiratoria) array_push($sintomas_arr, "dificultad respiratoria");
                    if ($sintoma->fiebre) array_push($sintomas_arr, "fiebre");
                    if ($sintoma->malestar_general) array_push($sintomas_arr, "malestar general");
                    if ($sintoma->diarrea) array_push($sintomas_arr, "diarrea");
                    if ($sintoma->anosmia_ausegia) array_push($sintomas_arr, "anosmia", "ausegia");
                    if ($sintoma->nauseas_vomitos) array_push($sintomas_arr, "náuseas", "vómitos");
                    if ($sintoma->congestion_nasal) array_push($sintomas_arr, "congestión nasal");
                    if ($sintoma->cefalea) array_push($sintomas_arr, "cefálea");
                    if ($sintoma->irritabilidad_confusion) array_push($sintomas_arr, "irritabilidad", "confusión");
                    if ($sintoma->falta_aliento) array_push($sintomas_arr, "falta de aliento");
                    if ($sintoma->otros) array_push($sintomas_arr, "otros: " . $sintoma->otros);
                    if ($sintoma->toma_medicamento) array_push($sintomas_arr, "toma medicamento: " . $sintoma->toma_medicamento);
                    if ($sintoma->fecha_inicio_sintomas) array_push($sintomas_arr, "fecha de inicio de síntomas: " . Carbon::parse($sintoma->fecha_inicio_sintomas)->format('d/m/Y'));
                    $sintomas_str = implode(", ", $sintomas_arr);
                }
            }
            if (count($ficha->AntecedentesEp) > 0) {
                foreach ($ficha->AntecedentesEp as $ant) {
                    $ant_arr = [];
                    if ($ant->contacto_cercano) array_push($ant_arr, "contacto cercano con investigado covid");
                    if ($ant->fecha_ultimo_contacto) array_push($ant_arr, "fecha de último contacto: " . $ant->fecha_ultimo_contacto);
                    if ($ant->conv_covid) array_push($ant_arr, "conversación con investigado covid");
                    if ($ant->dias_viaje) array_push($ant_arr, "ha viajadado en los últimos 14 días");
                    if ($ant->paises_visitados) array_push($ant_arr, "lugares visitados: " . $ant->paises_visitados);
                    if ($ant->fecha_llegada) array_push($ant_arr, "fecha de llegada viaje: " . $ant->fecha_llegada);
                    if ($ant->medio_transporte) array_push($ant_arr, "medio de transporte utilizado: " . $ant->medio_transporte);
                    if ($ant->debilite_sistema) array_push($ant_arr, "condición que debilite sistema: " . $ant->debilite_sistema);
                    $ant_str = implode(", ", $ant_arr);
                }
            }

            $constancia = [
                'id' => $request->id,
                'nom_completo' => $pac->nombres . " " . $pac->apellido_paterno . " " . $pac->apellido_materno,
                'edad' => Carbon::parse($pac->fecha_nacimiento)->age,
                'nro_documento' => $pac->numero_documento,
                'empresa' => $pac->Empresa->descripcion,
                'fecha' => Carbon::parse($ficha->created_at)->format('d/m/Y'),
                'ae' => Str::upper($ant_str),
                'dc' => Str::upper($sintomas_str),
                'res' => $ficha->PruebaAntigena[0]->resultado,
                'url' => COVID_URL . '/' . $request->id,
            ];

            $cons = new DescargasConstancia;
            $cons->dni = $pac->numero_documento;
            $cons->fecha = date('Y-m-d');
            $cons->fechaNacimiento = $pac->fecha_nacimiento;
            $cons->estado = 1;
            $cons->mensaje = 'DESCARGADO';
            $cons->save();

            $pdf = \PDF::loadView('pdf.certificadoAG', compact('constancia'));
            return $pdf->stream('certificado_ag' . $request->id . '.pdf');
            //dd($ficha);
        } else {
            abort(404);
        }
    }

    public function descargarStickerPrs(Request $request) {
        $prs = PruebaSerologica::findOrFail($request->id);
        $service = new PruebaSerologicaService($prs);
        $service->saveImageConstancias();

        return response()->download(public_path($service->getStickerShortPath()));
    }

    public function solicitarConstancia(Request $request)
    {
        
        $validated = $request->validate([
            'dni' => 'required',
	        'nombres' => 'required',
	        'apellidos' => 'required',
	        'email' => 'required|email',
	        'celular' => 'required',
	        'fechaNacimiento' => 'required|date_format:Y-m-d',
            'mensaje' => 'required'
        ]);

        SolicitudConstancia::create($validated);

        // Mail::to('betty.vega@isos-peru.com')
        //     ->cc(['cristhian.vargas@isos-peru.com', 'marco.bautista@isos-peru.com', 'lily.aguilar@isos-peru.com', 'edgard.soto@isos-peru.com'])
        //     ->send(new SendMailable($validated));

        return response([
            'message' => 'Solicitud enviada correctamente'
        ]);
    }
    public function enviarSolicitarConstancia()
    {
        Mail::to('cristhian.vargas@isos-peru.com')
            ->cc(['helpdesk@isos-peru.com'])
            ->queue(new SendMailable());
    }

    public function pruebasBot(Request $request){

        dd(env('APP_URL'));
    }

    public function reporteWhatsapp(Request $request) {
        $fichas = FichaPaciente::whereBetween(DB::raw('date(created_at)'), ['2021-06-01','2021-06-07'])
            ->whereHas('Estacion', function (Builder $q) {
                $q->whereIn('idsede', [1, 2, 3, 6]);
            })
            ->with(['PacienteIsos' => function ($q) {
                $q->select('idpacientes','numero_documento','nombres','apellido_paterno','apellido_materno','tipo_documento','idempresa')
                    ->with('Empresa:idempresa,descripcion');
            }])
            ->with(["PruebaSerologica" => function ($q) {
                $q->select('idpruebaserologicas','idfichapacientes','p1_positivo_recuperado','p1_react1gm','p1_reactigg'
                    ,'p1_reactigm_igg','no_reactivo','created_at','p1_positivo_persistente',
                    'p1_positivo_vacunado')
                    ->where("invalido", 0)
                    ->whereNotNull("no_reactivo")
                    ->with('EnvioWP.consulta')
                    ->latest();
            }])
            ->with(["PcrPruebaMolecular" => function ($q) {
                $q->whereNotnull("resultado")
                    ->where("resultado","<>", 2)
                    ->with('EnvioWpPcr.consulta');
            }])
            ->get();

        $contador = 1;

        foreach ($fichas as $ficha) {
            if (count($ficha->PruebaSerologica) > 0) {
                foreach ($ficha->PruebaSerologica as $ps) {
                    try {
                        $service = new PruebaSerologicaService($ps);
                        $ps->resultado = $service->strResult();
                    } catch (\Exception $exception){
                        //
                    }
                }
            }
            if ($ficha->PcrPruebaMolecular) {
                $ficha->PcrPruebaMolecular->resultado = getStrPcrResult($ficha->PcrPruebaMolecular->resultado);
            }
            $ficha->contador = $contador;
            $ficha->PacienteIsos->tipo_doc = getStrTipoDocumento($ficha->PacienteIsos->tipo_documento);
            ++$contador;
        }

        return Excel::download(new ExportReporteWhastApp($fichas), 'reporte.xlsx');
    }
}
