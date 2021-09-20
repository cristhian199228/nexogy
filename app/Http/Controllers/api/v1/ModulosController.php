<?php

namespace App\Http\Controllers\api\v1;

use App\EvidenciaRC;
use App\FichaPaciente;
use App\LlamadaNexogy;
use App\Http\Controllers\Controller;
use App\PacienteIsos;
use App\Service\FiltroService;
use App\Service\PruebaSerologicaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ModulosController extends Controller
{
    //
    public function admision(Request $request)
    {
        $buscar = $request->buscar;

        $fichas = FichaPaciente::where('id_estacion', $request->estacion)
            ->where(DB::raw('DATE(created_at)'), now()->format('Y-m-d'))
            ->whereHas("PacienteIsos", function ($q) use ($buscar) {
                $q->search($buscar);
            })
            ->with(["PacienteIsos" => function ($q) {
                $q->select('idpacientes', 'nombres', 'apellido_paterno', 'apellido_materno', 'idempresa')
                    ->with("Empresa:idempresa,descripcion");
            }])
            ->with("DatosClinicos")
            ->with("AntecedentesEp")
            ->with('DeclaracionJurada')
            ->with('ConsentimientoInformado')
            ->with("Temperatura")
            ->latest()
            ->paginate(15);

        $pagina = $fichas->currentPage();
        $total = $fichas->total();
        $contador = $total - (15 * ($pagina - 1));

        foreach ($fichas as $ficha) {
            $paciente = $ficha->PacienteIsos;
            $nom_completo = $paciente->nombres . " " . $paciente->apellido_paterno . " " . $paciente->apellido_materno;
            $paciente->nom_completo = Str::upper($nom_completo);
            $ficha->contador = $contador;
            $contador--;
        }

        return $fichas;
    }

    public function controlador(Request $request)
    {

        $buscar = $request->buscar;

        $fichas = FichaPaciente::whereDate('created_at', date('Y-m-d'))
            ->where('id_estacion', $request->estacion)
            ->whereHas("PacienteIsos", function ($q) use ($buscar) {
                $q->search($buscar);
            })
            ->with("PacienteIsos:idpacientes,nombres,apellido_paterno,apellido_materno,numero_documento,celular")
            ->with("AnexoTres:idanexotres,idfichapacientes,path")
            ->with(["PruebaSerologica" => function ($q) {
                $q->withCount('EnvioWP')->latest();
            }])
            ->latest()
            ->paginate(15);

        $pagina = $fichas->currentPage();
        $total = $fichas->total();
        $contador = $total - (15 * ($pagina - 1));

        foreach ($fichas as $ficha) {
            $ficha->contador = $contador;
            $contador--;
        }

        return $fichas;
    }

    public function pcr(Request $request)
    {

        $buscar = $request->buscar;

        $fichas = FichaPaciente::where(DB::raw('DATE(created_at)'), now()->format('Y-m-d'))
            ->where('id_estacion', $request->estacion)
            ->whereHas("PacienteIsos", function ($q) use ($buscar) {
                $q->search($buscar);
            })
            ->with(["PacienteIsos" => function ($q) {
                $q->select('idpacientes', 'nombres', 'apellido_paterno', 'apellido_materno', 'idempresa', 'numero_documento')
                    ->with("Empresa:idempresa,descripcion");
            }])
            ->with("PcrPruebaMolecular.FichaInvestigacion.FichaInvFoto", "PcrPruebaMolecular.PcrEnvioMunoz")
            ->with("AntecedentesEp")
            ->latest()
            ->paginate(15);

        $pagina = $fichas->currentPage();
        $total = $fichas->total();
        $contador = $total - (15 * ($pagina - 1));

        foreach ($fichas as $ficha) {
            $paciente = $ficha->PacienteIsos;
            $nom_completo = $paciente->nombres . " " . $paciente->apellido_paterno . " " . $paciente->apellido_materno;
            $paciente->nom_completo = Str::upper($nom_completo);
            $ficha->contador = $contador;
            $contador--;
        }

        return $fichas;
    }

    public function supervisorPrs(Request $request)
    {
        $collection = FichaPaciente::whereDate('created_at', now()->format('Y-m-d'))
            ->whereHas('Estacion', function ($q) use ($request) {
                $q->where('estaciones.idsede', $request->sede);
            })
            ->whereHas("PacienteIsos", function ($q) use ($request) {
                $q->search($request->buscar);
            });
        $filtro = new FiltroService($collection);

        if ($request->has('empresa')) {
            $filtro->empresa($request->empresa);
        }
        if ($request->has('estacion')) $collection->where('id_estacion', $request->estacion);
        if ($request->has('estado')) $collection->where('estado', $request->estado);
        if ($request->has('temperatura')) {
            $collection->whereHas('Temperatura', function (Builder $q) use ($request) {
                if ($request->temperatura) return $q->where('valortemp', '>=', 37.8);
                return $q->where('valortemp', '<', 37.8);
            });
        }
        if ($request->has('ps')) {
            $filtro->pruebaSerologica($request->ps);
        }
        if ($request->has('enviado_wp')) {
            $collection->whereHas('PruebaSerologica', function (Builder $q) use ($request) {
                if ($request->enviado_wp == 0) return $q->where('fichapacientes.enviar_mensaje', 1)->doesntHave('EnvioWP');
                else if ($request->enviado_wp == 1) return $q->has("EnvioWP");
                return $q->where('fichapacientes.enviar_mensaje', 0);
            });
        } else {
            $collection->has('PruebaSerologica');
        }

        $collection->with(["PruebaSerologica" => function ($q) {
            $q->select(
                'idpruebaserologicas',
                'idfichapacientes',
                'p1_positivo_recuperado',
                'p1_react1gm',
                'p1_positivo_persistente',
                'p1_reactigg',
                'p1_reactigm_igg',
                'no_reactivo',
                'created_at',
                'p1_positivo_vacunado'
            )
                ->withCount("EnvioWP")->where("invalido", 0)->whereNotNull("no_reactivo")->latest()->get();
        }])->with(["PacienteIsos" => function ($q) {
            $q->select('idpacientes', 'nombres', 'apellido_paterno', 'apellido_materno', 'idempresa', 'numero_documento', 'celular')->with("Empresa:idempresa,descripcion");
        }])->with("CitasMw", "Estacion.Sede", "DatosClinicos", "AntecedentesEp", 'Temperatura');

        $fichas = $collection->latest()->get();
        $contador = $fichas->count();

        foreach ($fichas as $ficha) {
            $ficha->contador = $contador;
            $ficha->Estacion->nom_estacion = getNomEstacion($ficha->Estacion);
            foreach ($ficha->PruebaSerologica as $ps) {
                try {
                    $ps->resultado = (new PruebaSerologicaService($ps))->result();
                } catch (\Exception $ex) {
                    //no-op
                }
            }
            $contador--;
        }

        return $fichas;
    }

    public function supervisorPcr(Request $request)
    {

        $buscar = $request->buscar;

        $fichas = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("PacienteIsos", function ($q) use ($buscar) {
                $q->search($buscar);
            })
            ->with(["PacienteIsos" => function ($q) {
                $q->select('idpacientes', 'nombres', 'apellido_paterno', 'apellido_materno', 'idempresa', 'numero_documento')
                    ->with("Empresa:idempresa,descripcion");
            }])
            ->with("Estacion.Sede")
            ->whereHas("PcrPruebaMolecular")
            ->with("PcrPruebaMolecular.FichaInvestigacion.FichaInvFoto", "PcrPruebaMolecular.PcrEnvioMunoz")
            ->latest()
            ->paginate(15);

        $pagina = $fichas->currentPage();
        $total = $fichas->total();
        $contador = $total - (15 * ($pagina - 1));

        foreach ($fichas as $ficha) {
            $paciente = $ficha->PacienteIsos;
            $nom_completo = $paciente->nombres . " " . $paciente->apellido_paterno . " " . $paciente->apellido_materno;
            $paciente->nom_completo = Str::upper($nom_completo);
            $ficha->Estacion->nom_estacion = getNomEstacion($ficha->Estacion);
            $ficha->contador = $contador;
            $contador--;
        }

        return $fichas;
    }

    public function salud(Request $request)
    {

        $pacientes = PacienteIsos::search($request->buscar)
            ->with(["FichaPaciente" => function ($q) {
                $q->select('idficha_paciente', 'id_paciente', 'created_at')
                    ->with(["PruebaSerologica" => function ($q) {
                        $q->select(
                            'idpruebaserologicas',
                            'idfichapacientes',
                            'p1_positivo_recuperado',
                            'p1_react1gm',
                            'p1_reactigg',
                            'p1_reactigm_igg',
                            'no_reactivo',
                            'created_at',
                            'p1_positivo_persistente',
                            'p1_positivo_vacunado'
                        )
                            ->where("invalido", 0)->whereNotNull("no_reactivo")->latest()->get();
                    }])
                    ->with("PcrPruebaMolecular", 'DatosClinicos', 'AntecedentesEp')->latest()->get();
            }])
            ->with('Empresa:idempresa,descripcion,nombrecomercial')
            ->latest()
            ->paginate(20);

        foreach ($pacientes as $paciente) {
            if (count($paciente->FichaPaciente) > 0) {
                foreach ($paciente->FichaPaciente as $ficha) {
                    $ficha->fecha = $ficha->created_at->format("d/m/y");
                    foreach ($ficha->PruebaSerologica as $ps) {
                        try {
                            $ps->resultado = (new PruebaSerologicaService($ps))->result();
                        } catch (\Exception $ex) {
                            //no-op
                        }
                    }
                }
            }
        }

        return $pacientes;
    }

 

    public function administradorPcr(Request $request)
    {

        /*$collection = LlamadaNexogy::whereBetween(DB::raw('date(created_at)'), [
                $request->fecha_inicio,
                $request->fecha_final
            ])*/

        $collection = LlamadaNexogy::whereBetween(DB::raw('date(StartTime)'), [
            $request->fecha_inicio,
            $request->fecha_final
        ])
            /*->whereHas("PacienteIsos", function($q) use ($request) {
                $q->search($request->buscar);
            })
            ->with("PcrPruebaMolecular.PcrEnvioMunoz",
                "PcrPruebaMolecular.PcrFotoMuestra",
                'PacienteIsos.Empresa',
                'Estacion.Sede'
            )*/;

        //dd($collection->get());

        $filtro = new FiltroService($collection);

        if ($request->has('desde')) {
            $filtro->desde($request->desde);
        }
        if ($request->has('para')) {
            $filtro->para($request->para);
        }
        if ($request->has('direccion')) {
            $filtro->direccion($request->direccion);
        }
/*
        if ($request->has('resultado')) {
            $filtro->pruebaMolecular($request->resultado);
        } else {
            $collection->has('PcrPruebaMolecular');
        }

        if ($request->has('tipo')) {
            $collection->whereHas('PcrPruebaMolecular', function (Builder $q) use ($request){
                return $q->where('tipo', $request->tipo);
            });
        }

        if ($request->has('sede')) {
            $collection->whereHas('Estacion.Sede', function (Builder $q) use ($request){
                return $q->where('idsedes', $request->sede);
            });
        }
*/
        $fichas = $collection->oldest()->paginate(15);

        $pagina = $fichas->currentPage();
        $total = $fichas->total();
        $contador = $total - (15 * ($pagina - 1));

        //echo gmdate("H:i:s", 685);

        foreach ($fichas as $ficha) {
            $ficha->contador = $contador;
           /* $NuevaFecha = strtotime ( '-5 hour' , strtotime ($ficha->StartTime) ) ; 
            $NuevaFecha = date ( 'd-m-Y h:i:s A' , $NuevaFecha); 
            $ficha->fecha = $NuevaFecha;*/
            //$ficha->fecha = $ficha->created_at->format('d/m/Y');
            $ficha->fecha = $ficha->StartTime->format('d/m/Y h:i:s A');
            $ficha->duracion = gmdate("H:i:s", $ficha->Duration);
            $contador--;
        }

        return $fichas;
    }

    public function responceCenter(Request $request)
    {

        $collection = EvidenciaRC::whereHas("paciente", function ($q) use ($request) {
            $q->search($request->buscar);
        })
            /*->whereBetween(DB::raw('date(created_at)'), [
                $request->fecha_inicio,
                $request->fecha_final
            ])*/
            ->with('paciente.Empresa', 'fichaEp.contactos', 'fichaCam', 'estacion')
            ->withCount('indicaciones');

        $service = new FiltroService($collection);

        if ($request->has('estacion')) {
            $collection->whereHas('estacion', function (Builder $q) use ($request) {
                return $q->where('idestaciones', $request->estacion);
            });
        }

        if ($request->has('sede')) {
            $service->sede($request->sede);
        }

        if ($request->has('empresa')) {
            $collection->whereHas('paciente.Empresa', function (Builder $q) use ($request) {
                $q->where('descripcion', 'LIKE', '%' . $request->empresa . '%')
                    ->orWhere('nombrecomercial', 'LIKE', '%' . $request->empresa . '%')
                    ->orWhere('ruc', 'LIKE', '%' . $request->empresa . '%');
            });
        }

        $evidencias = $collection->latest()->paginate(30);

        $pagina = $evidencias->currentPage();
        $total = $evidencias->total();
        $contador = $total - (30 * ($pagina - 1));

        foreach ($evidencias as $ev) {
            $ev->contador = $contador;
            $paciente = $ev->paciente;
            $nom_completo = $paciente->nombres . " " . $paciente->apellido_paterno . " " . $paciente->apellido_materno;
            $paciente->nom_completo = Str::upper($nom_completo);
            $ev->fecha = $ev->created_at ? $ev->created_at->format('d/m/y H:i') : '';
            $pos = strpos($ev->usuario, "@");
            $str = Str::substr($ev->usuario, 0, $pos);
            $arr_str = explode(".", $str);
            $ev->user = Str::upper(implode(" ", $arr_str));
            $ev->estacion->nom = getNomEstacion($ev->estacion);
            $pdfs = [];
            $imgs = [];
            foreach ($ev->fotos as $foto) {
                $strLength = Str::length($foto->path);
                $extension = Str::substr($foto->path, $strLength - 3, $strLength);
                if ($extension == 'pdf') {
                    array_push($pdfs, $foto);
                } else {
                    array_push($imgs, $foto);
                }
            }
            unset($ev->fotos);
            $ev->pdfs = $pdfs;
            $ev->imgs = $imgs;
            $contador--;
        }

        return $evidencias;
    }

    public function controladorPa(Request $request)
    {

        $fichas = FichaPaciente::whereDate('created_at', date('Y-m-d'))
            ->where('id_estacion', $request->id_estacion)
            ->whereHas("PacienteIsos", function ($q) use ($request) {
                $q->search($request->buscar);
            })
            ->with(['pruebaAntigena' => function ($q) {
                $q->withCount('envio')
                    ->latest();
            }])
            ->with(["PacienteIsos" => function ($q) {
                $q->select('idpacientes', 'nombres', 'apellido_paterno', 'apellido_materno', 'idempresa', 'numero_documento')
                    ->with("Empresa:idempresa,descripcion");
            }])
            ->with("AnexoTres:idanexotres,idfichapacientes,path")
            ->latest()->paginate(15);

        $pagina = $fichas->currentPage();
        $total = $fichas->total();
        $contador = $total - (15 * ($pagina - 1));

        foreach ($fichas as $ficha) {
            $ficha->contador = $contador;
            $contador--;
        }

        return $fichas;
    }
}
