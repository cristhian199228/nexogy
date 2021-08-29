<?php

namespace App\Http\Controllers\api\v1;

use App\EvidenciaRC;
use App\Exports\ExportReporteComplejo;
use App\Exports\ExportReporteResponceCenter;
use App\Exports\ExportReporteSalud;
use App\Exports\NuevoSupervisorPcrExport;
use App\FichaPaciente;
use App\Http\Controllers\Controller;
use App\PruebaSerologica;
use App\Service\PruebaAntigenaService;
use App\Service\PruebaSerologicaService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ReportesController extends Controller
{
    //
    public function supervisorPcr(Request $request) {

        $fichas = FichaPaciente::whereBetween(DB::raw('DATE(created_at)'), [$request->inicio, $request->final])
            ->whereHas("PacienteIsos")
            ->with(["PacienteIsos" => function($q) {
                $q->select('idpacientes','nombres','apellido_paterno','apellido_materno','idempresa','numero_documento')
                    ->with("Empresa:idempresa,descripcion");
            }])
            ->with("Estacion.Sede")
            ->whereHas("PcrPruebaMolecular")
            ->with("PcrPruebaMolecular.FichaInvestigacion.FichaInvFoto", "PcrPruebaMolecular.PcrEnvioMunoz")
            ->latest()
            ->get();

        foreach ($fichas as $ficha) {
            $paciente = $ficha->PacienteIsos;
            $nom_completo = $paciente->nombres . " " .$paciente->apellido_paterno . " " .$paciente->apellido_materno;
            $hora_fin = "";
            $estado_fotos = "SIN FOTOS";

            if ($ficha->PcrPruebaMolecular->hora_fin) $hora_fin = Carbon::parse($ficha->PcrPruebaMolecular->hora_fin)->format('H:i');
            if ($ficha->PcrPruebaMolecular->FichaInvestigacion && $ficha->PcrPruebaMolecular->FichaInvestigacion->FichaInvFoto) {
                $f1 = $ficha->PcrPruebaMolecular->FichaInvestigacion->FichaInvFoto->path;
                $f2 = $ficha->PcrPruebaMolecular->FichaInvestigacion->FichaInvFoto->path2;
                if ($f1 && $f2) $estado_fotos = "SI";
                else if ($f1 && !$f2) $estado_fotos = "FALTA FOTO REVERSO";
                else $estado_fotos = "FALTA FOTO ANVERSO";
            }

            $ficha->estado_fotos = $estado_fotos;
            $ficha->Estacion->nom_estacion = getNomEstacion($ficha->Estacion);
            $ficha->PcrPruebaMolecular->resultado = getStrPcrResult($ficha->PcrPruebaMolecular->resultado);
            $ficha->PcrPruebaMolecular->hora_fin = $hora_fin;
            $ficha->PcrPruebaMolecular->hora_inicio = Carbon::parse($ficha->PcrPruebaMolecular->created_at)->format('H:i');
            $ficha->turno = getStrTurno($ficha->turno);
            $ficha->rol = getStrRol($ficha->rol);
            $ficha->fecha = Carbon::parse($ficha->created_at)->format('d-m-Y');
            $paciente->nom_completo = Str::upper($nom_completo);
        }

        $finicio = Str::of($request->inicio)->replace("-","");
        $ffinal= Str::of($request->final)->replace("-","");
        $nom_archivo = 'pcr'.$finicio."-".$ffinal.'.xlsx';

        return Excel::download(new NuevoSupervisorPcrExport($fichas), $nom_archivo);
    }

    public function salud(Request $request) {

        $f_inicio = $request->inicio;
        $f_final = $request->final;

        $fichas =  FichaPaciente::whereBetween(DB::raw('DATE(created_at)'), [$f_inicio, $f_final])
            ->whereHas("PacienteIsos")
            ->with(["PacienteIsos" => function($q) {
                $q->select('idpacientes','tipo_documento','numero_documento','puesto',
                    'nombres','apellido_paterno','apellido_materno','direccion','fecha_nacimiento','idempresa','celular','correo')
                    ->with("Empresa:idempresa,descripcion");
            }])
            ->with(["PruebaSerologica" => function ($q) {
                $q->select('idpruebaserologicas','idfichapacientes','p1_positivo_recuperado','p1_react1gm','p1_reactigg'
                    ,'p1_reactigm_igg','no_reactivo','p1_positivo_persistente','p1_positivo_vacunado')
                    ->where("invalido", 0)
                    ->whereNotNull("no_reactivo")
                    ->latest();
            }])
            ->with(["pruebaAntigena" => function ($q) {
                $q->select('id','idficha_paciente','resultado')
                    ->whereNotNull("resultado")
                    ->latest();
            }])
            ->with("Estacion.Sede","DatosClinicos","AntecedentesEp","Temperatura")
            ->latest()
            ->get();

        foreach ($fichas as $ficha) {
            $paciente = $ficha->PacienteIsos;

            foreach ($ficha->PruebaSerologica as $ps) {
                $ps->resultado = (new PruebaSerologicaService($ps))->strResult();
            }

            foreach ($ficha->pruebaAntigena as $pa) {
                $pa->resultado = (new PruebaAntigenaService($pa))->strResult();
            }

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
                    if ($sintoma->fecha_inicio_sintomas) array_push($sintomas_arr, "fecha de inicio de síntomas: " . $sintoma->fecha_inicio_sintomas);
                    $sintoma->sintomas_str = implode(", ", $sintomas_arr);
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
                    $ant->ant_str = implode(", ", $ant_arr);
                }
            }

            if ($ficha->PcrPruebaMolecular) $ficha->PcrPruebaMolecular->resultado = getStrPcrResult($ficha->PcrPruebaMolecular->resultado);
            $ficha->Estacion->nom_estacion = getNomEstacion($ficha->Estacion);
            $paciente->nom_completo = $paciente->nombres . " " . $paciente->apellido_paterno . " " . $paciente->apellido_materno;
            $paciente->edad = Carbon::parse($paciente->fecha_nacimiento)->age;
            $paciente->tipo_documento = getStrTipoDocumento($paciente->tipo_documento);
            $ficha->turno = getStrTurno($ficha->turno);
            $ficha->rol = getStrRol($ficha->rol);
            $ficha->fecha = Carbon::parse($ficha->created_at)->format('d-m-Y');
        }

        $finicio = Str::of($f_inicio)->replace("-","");
        $ffinal= Str::of($f_final)->replace("-","");
        $nom_archivo = 'salud'.$finicio."-".$ffinal.'.xlsx';
        //return $fichas;
        return Excel::download(new ExportReporteSalud($fichas), $nom_archivo);
    }

    public function complejo(Request $request) {

        $fichas_cv = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa", 7);
            })
            ->whereDoesntHave('DatosClinicos', function (Builder $q) {
                $q->where('post_vacunado', 1);
            })
            ->count();

        $fichas_con = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa","<>", 7);
            })
            ->whereDoesntHave('DatosClinicos', function (Builder $q) {
                $q->where('post_vacunado', 1);
            })
            ->count();

        $fichas_isos = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa", 12);
            })
            ->count();

        $reactivos_igm_cv = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa", 7);
            })
            ->whereHas("PruebaSerologica", function($q) {
                $q->where("p1_react1gm", 1)->where("p1_positivo_persistente", 0);
            })
            ->count();

        $reactivos_igm_con = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa","<>", 7);
            })
            ->whereHas("PruebaSerologica", function($q) {
                $q->where("p1_react1gm", 1)->where("p1_positivo_persistente", 0);
            })
            ->count();

        $reactivos_igm_igg_cv = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa", 7);
            })
            ->whereHas("PruebaSerologica", function($q) {
                $q->where("p1_reactigm_igg", 1)->where("p1_positivo_persistente", 0);
            })
            ->count();

        $reactivos_igm_igg_con = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa","<>", 7);
            })
            ->whereHas("PruebaSerologica", function($q) {
                $q->where("p1_reactigm_igg", 1)->where("p1_positivo_persistente", 0);
            })
            ->count();

        $reactivos_igg_cv = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa", 7);
            })
            ->whereHas("PruebaSerologica", function($q) {
                $q->where("p1_reactigg", 1)
                    ->where("p1_positivo_recuperado", 0)->where("p1_positivo_vacunado", 0);
            })
            ->count();

        $reactivos_igg_con = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa","<>", 7);
            })
            ->whereHas("PruebaSerologica", function($q) {
                $q->where("p1_reactigg", 1)
                    ->where("p1_positivo_recuperado", 0)->where("p1_positivo_vacunado", 0);
            })
            ->count();

        $reactivos_igg_recuperados_cv = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa", 7);
            })
            ->whereHas("PruebaSerologica", function($q) {
                $q->where("p1_reactigg", 1)
                    ->where("p1_positivo_recuperado", 1);
            })
            ->count();

        $reactivos_igg_recuperados_con = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa",'<>', 7);
            })
            ->whereHas("PruebaSerologica", function($q) {
                $q->where("p1_reactigg", 1)
                    ->where("p1_positivo_recuperado", 1);
            })
            ->count();

        $reactivos_igg_vacunados_cv = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa", 7);
            })
            ->whereHas("PruebaSerologica", function($q) {
                $q->where("p1_reactigg", 1)
                    ->where("p1_positivo_vacunado", 1);
            })
            ->count();

        $reactivos_igg_vacunados_con = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa",'<>', 7);
            })
            ->whereHas("PruebaSerologica", function($q) {
                $q->where("p1_reactigg", 1)
                    ->where("p1_positivo_vacunado", 1);
            })
            ->count();

        $sintomaticos_cv = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa", 7);
            })
            ->has("DatosClinicos")
            ->count();

        $sintomaticos_con = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa",'<>', 7);
            })
            ->has("DatosClinicos")
            ->count();

        $epidemiologicos_cv = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa", 7);
            })
            ->has("AntecedentesEp")
            ->count();

        $epidemiologicos_con = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa",'<>', 7);
            })
            ->has("AntecedentesEp")
            ->count();

        $no_reactivos_cv = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa", 7);
            })
            ->whereHas("PruebaSerologica", function($q) {
                $q->where("no_reactivo", 1);
            })
            ->count();

        $no_reactivos_con = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa",'<>', 7);
            })
            ->whereHas("PruebaSerologica", function($q) {
                $q->where("no_reactivo", 1);
            })
            ->count();

        $pruebas_repetidas_cv = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa", 7);
            })
            ->has("PruebaSerologica",">",1)
            ->count();

        $pruebas_repetidas_con = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa",'<>', 7);
            })
            ->has("PruebaSerologica",">",1)
            ->count();

        $pruebas_moleculares_cv = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa", 7);
            })
            ->has("PcrPruebaMolecular")
            ->count();

        $pruebas_moleculares_con = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })
            ->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa",'<>', 7);
            })
            ->has("PcrPruebaMolecular")
            ->count();

        $persistentes_cv = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa", 7);
            })->whereHas("PruebaSerologica", function($q) {
                $q->where("p1_positivo_persistente", 1);
            })->count();

        $persistentes_con = FichaPaciente::where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->whereHas("Estacion", function($q) {
                $q->where('idsede', 3);
            })->whereHas("PacienteIsos", function($q) {
                $q->where("idempresa",'<>', 7);
            })->whereHas("PruebaSerologica", function($q) {
                $q->where("p1_positivo_persistente", 1);
            })->count();

        $reporte = [
            'cv' => [
                'fichas' => $fichas_cv,
                'igm' => $reactivos_igm_cv,
                'igm_igg' => $reactivos_igm_igg_cv,
                'igg' => $reactivos_igg_cv,
                'igg_rec' => $reactivos_igg_recuperados_cv,
                'igg_vac' => $reactivos_igg_vacunados_cv,
                'persistentes' => $persistentes_cv,
                'sintomaticos' => $sintomaticos_cv,
                'epidemiologicos' => $epidemiologicos_cv,
                'no_reactivos' => $no_reactivos_cv,
                'pruebas_repetidas' => $pruebas_repetidas_cv,
                'pruebas_moleculares' => $pruebas_moleculares_cv,
            ],
            'con' => [
                'fichas' => $fichas_con,
                'igm' => $reactivos_igm_con,
                'igm_igg' => $reactivos_igm_igg_con,
                'igg' => $reactivos_igg_con,
                'igg_rec' => $reactivos_igg_recuperados_con,
                'igg_vac' => $reactivos_igg_vacunados_con,
                'persistentes' => $persistentes_con,
                'sintomaticos' => $sintomaticos_con,
                'epidemiologicos' => $epidemiologicos_con,
                'no_reactivos' => $no_reactivos_con,
                'pruebas_repetidas' => $pruebas_repetidas_con,
                'pruebas_moleculares' => $pruebas_moleculares_con,
            ],
            'isos' => [
                'fichas' => $fichas_isos
            ]
        ];

        //return $reporte;

        return Excel::download(new ExportReporteComplejo($reporte), 'reporte.xlsx');
    }

    public function responceCenter(Request $request) {

        $evidencias = EvidenciaRC::whereBetween(DB::raw('DATE(created_at)'), [$request->inicio, $request->final])
            ->with('paciente.Empresa')
            ->with('fichaEp.contactos')
            ->with('fichaCam')
            ->with('fotos')
            ->with('estacion')
            ->latest()
            ->get();

        $contador = $evidencias->count();

        foreach ($evidencias as $ev) {
            $ev->contador = $contador;
            $paciente = $ev->paciente;
            $nom_completo = $paciente->nombres . " " .$paciente->apellido_paterno . " " .$paciente->apellido_materno;
            $paciente->nom_completo = Str::upper($nom_completo);
            $pos = strpos($ev->usuario, "@");
            $str = Str::substr($ev->usuario, 0, $pos);
            $arr_str = explode(".", $str);
            $ev->user = Str::upper(implode(" ", $arr_str));
            $ev->fecha = $ev->created_at->format('d/m/Y H:i');
            $ev->estacion->nom_estacion = getNomEstacion($ev->estacion);
            $contador--;
        }
        //return  $evidencias;

        return Excel::download(new ExportReporteResponceCenter($evidencias), 'reporte.xlsx');
    }
}
