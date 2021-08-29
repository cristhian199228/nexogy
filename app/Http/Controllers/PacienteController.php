<?php

namespace App\Http\Controllers;

use App\CitasAutomaticasMw;
use App\CitasMwSupervisor;
use App\ExcelNicole;
use App\PacienteIsos;
use App\PacientePorRegistrar;
use App\PcrPruebaMolecular;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use KubAT\PhpSimple\HtmlDomParser;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Auto;
use App\Lote;
use App\Manzana;
use App\Paciente;
use App\PersonaZap;
use App\Comprobante;
use App\ExamenOcu;
use App\DepartamentoPeru;
use App\Provincia;
use App\Distrito;
use App\FichaPaciente;
use App\Empresa;
use App\DatosClinicos;
use App\PruebaSerologica;
use App\AntecedentesEp;
use App\Temperatura;
use App\DeclaracionJurada;
use App\ConsentimientoInformado;
use App\FichasPasadas;
use App\AnexoTres;
use App\Estacion;
use App\Sede;
use Illuminate\Support\Str;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AutosExport;
use App\Exports\SaludExport;
use App\Exports\YadiraExport;
use App\Exports\NuevoSaludExport;
use SoapClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class PacienteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // if (!$request->ajax()) return redirect('/');
        $buscar = $request->buscar;
        $fechaActual = date('Y-m-d');
        $fechaActual2 = '2020-06-25';
        $criterio = $request->criterio;
        $sede = $request->sede;

        $fichass = FichaPaciente::where('fichapacientes.fecha', $fechaActual)
            ->with('Empresa:idempresa,descripcion,nombrecomercial')
            ->whereHas(
                'Estacion',
                function ($q) use ($sede) {
                    $q->where('estaciones.idsede', $sede);
                }
            )
            ->with("Estacion.Sede")
            ->with("DatosClinicos")
            ->with("AntecedentesEp")
            //->with("PruebaSerologica")
            ->with(['PruebaSerologica' => function ($q) {
                $q->orderBy('pruebaserologicas.created_at', 'DESC');
            }])
            ->with("Temperatura")
            ->orderBy('fichapacientes.created_at', 'DESC')
            ->get();

        $contador = $fichass->count() + 1;
        foreach ($fichass as $ficha) {
            $contador = $contador - 1;
            $ficha->contador = $contador;
        }

        return [

            'categorias' => $fichass
        ];
    }

    public function MandarCorreoPacientes(Request $request)
    {
        // if (!$request->ajax()) return redirect('/');
        $buscar = $request->buscar;
        $fechaActual = date('Y-m-d');
        $fecha =  date("Y-m-d", strtotime($fechaActual . "+ 1 days"));
        $criterio = $request->criterio;
        $sede = $request->sede;

        //$comprobante = Comprobante::select('paciente.dni2','paciente.nombres','paciente.apellidos')
        $comprobante = Comprobante::select('paciente.dni2')
            ->join('paciente', 'comprobante.idpaciente', '=', 'paciente.idpaciente')
            ->where('comprobante.estado', "=", '4')
            ->where('comprobante.fecha', '=', $fecha)
            ->get();
        print($comprobante);
        foreach ($comprobante as $cita) {

            $fichass = FichaPaciente::where('fichapacientes.dni', $cita)
                //->where('fichapacientes.nombre_completo','like','%edwin quispe%')
                //->with("Empresa")
                ->with('Empresa:idempresa,descripcion,nombrecomercial')
                ->whereHas(
                    'Estacion',
                    function ($q) use ($sede) {
                        $q->where('estaciones.idsede', $sede);
                    }
                )
                /*->whereHas(["Estacion.Sede" => function ($q) use ($sede) {
                $q->where('sedes.idsedes',$sede);
                //$q->where('some other field', $someId);
            }])*/
                ->with("Estacion.Sede")
                ->with("DatosClinicos")
                ->with("AntecedentesEp")
                //->with("PruebaSerologica")
                ->with(['PruebaSerologica' => function ($q) {
                    $q->orderBy('pruebaserologicas.created_at', 'DESC');
                }])
                ->with("Temperatura")
                ->orderBy('fichapacientes.created_at', 'DESC')
                ->get();
        }
        $contador = $fichass->count() + 1;
        foreach ($fichass as $ficha) {
            $contador = $contador - 1;
            $ficha->contador = $contador;
        }

        return [

            'categorias' => $fichass
        ];
    }

    public function export(Request $request)
    {

        $fechaActual = date('Y-m-d');
        $fichas = FichaPaciente::select(
            'fichapacientes.idficha_paciente',
            'sedes.descripcion',
            'fichapacientes.fecha',
            'fichapacientes.nombre_completo',
            'fichapacientes.idtipodocumento',
            'fichapacientes.dni',
            'fichapacientes.numero_registro',
            'empresa.descripcion as empresa',
            'fichapacientes.puesto'
        )
            ->leftjoin('empresa', 'empresa.idempresa', '=', 'fichapacientes.idempresa')
            ->leftjoin('estaciones', 'fichapacientes.id_estacion', '=', 'estaciones.idestaciones')
            ->leftjoin('sedes', 'sedes.idsedes', '=', 'estaciones.idsede')

            ->whereBetween('fichapacientes.fecha', [$request->inicio, $request->final])
            ->orderBy('fichapacientes.fecha', 'ASC')
            ->get();






        //
        //echo ($contador);
        //dd($fichas);
        foreach ($fichas as $ficha) {
            $id =  $ficha->idficha_paciente;

            if ($ficha->puesto == "") {
                $ficha->puesto = $ficha->puesto_mw;
            }

            if ($ficha->empresa == null) {
                $ficha->empresa = 'SOCIEDAD MINERA CERRO VERDE S.A.A.';
            }

            //dd($ficha);
            //dd($ficha->anamnesis);
            if (is_null($ficha->anamnesis)) {

                // dd($ficha);
                $datosclinicos = DatosClinicos::where('idfichapacientes', $id)
                    ->where(function ($q) {
                        $q->where('tos', 1)
                            ->orWhere('dolor_garganta', 1)
                            ->orWhere('dificultad_respiratoria', 1)
                            ->orWhere('fiebre', 1)
                            ->orWhere('malestar_general', 1)
                            ->orWhere('diarrea', 1)
                            ->orWhere('anosmia_ausegia', 1)
                            ->orwhereNotNull('otros')
                            ->orwhereNotNull('toma_medicamento')
                            ->orwhereNotNull('debilite_sistema')
                            ->orWhere('nauseas_vomitos', 1)
                            ->orWhere('congestion_nasal', 1)
                            ->orWhere('cefalea', 1)
                            ->orWhere('irritabilidad_confusion', 1)
                            ->orWhere('falta_aliento', 1);
                    })
                    ->count();

                if ($datosclinicos == 0) {
                    $datosclinicos = DatosClinicos::where('idfichapacientes', $id)
                        ->first();

                    if (is_null($datosclinicos)) {
                        $datosclinicos = "NO";
                    } else {
                        $datosclinicos = "NO";
                    }
                } else {
                    $datosclinicos = "SI";
                }


                $ficha->anamnesis = $datosclinicos;
            }
            if (is_null($ficha->antecedentes_ep)) {


                $antecedentesep = AntecedentesEp::where('idfichapacientes', $id)
                    ->where(function ($q) {
                        $q->where('dias_viaje', 1)
                            ->orWhere('contacto_cercano', 1)
                            ->orWhere('conv_covid', 1)
                            ->orwhereNotNull('paises_visitados');
                    })
                    ->count();


                if ($antecedentesep == 0) {
                    $antecedentesep = AntecedentesEp::where('idfichapacientes', $id)
                        ->first();

                    if (is_null($antecedentesep)) {
                        $antecedentesep = "NO";
                    } else {
                        $antecedentesep = "NO";
                    }
                } else {
                    $antecedentesep = "SI";
                }


                $ficha->antecedentes_ep = $antecedentesep;
            }



            $temperatura = Temperatura::where('idfichapacientes', $id)
                ->first();
            //dd($temperatura);
            if (is_null($temperatura)) {
                $ficha->temperatura = '36.6';
            } else {
                $ficha->temperatura = $temperatura->valortemp;
            }

            //dd($temperatura);



            if (is_null($ficha->prueba_serologica)) {
                $pruebaseriologica = PruebaSerologica::where('idfichapacientes', $id)
                    ->orderBy('created_at', 'DESC')
                    ->first();
                //dd($pruebaseriologica);
                //print_r($pruebaseriologica->p1_react1gm);

                if (is_null($pruebaseriologica)) {
                    $pruebaseriologicavalor = "NEGATIVO";
                } else {
                    if ($pruebaseriologica->p1_positivo_recuperado == 1) {
                        $pruebaseriologicavalor = "POSITIVO RECUPERADO";
                    } else {
                        if ($pruebaseriologica->p1_react1gm == '0' &&  $pruebaseriologica->p1_reactigg == '0' && $pruebaseriologica->p1_reactigm_igg == '0') {
                            //dd(pruebaseriologica);
                            $pruebaseriologicavalor = "NEGATIVO";
                        } else {
                            if ($pruebaseriologica->p1_react1gm == null &&  $pruebaseriologica->p1_reactigg == null && $pruebaseriologica->p1_reactigm_igg == null) {
                                $pruebaseriologicavalor = "NEGATIVO";
                            } else {
                                $pruebaseriologicavalor = "POSITIVO";
                            }
                        }
                    }
                }


                $ficha->prueba_serologica = $pruebaseriologicavalor;
            }





            //return Excel::download(new MttRegistrationsExport($request->id), 'MttRegistrations.xlsx');

        }
        return Excel::download(new AutosExport($fichas), 'examenes.xlsx');
        /*return [

            'categorias' => $fichas
        ];*/
    }


    public function exportSalud(Request $request)
    {

        $fechaActual = date('Y-m-d');
        $fichas = FichaPaciente::select(
            'fichapacientes.idficha_paciente',
            'fichapacientes.fecha',
            'estaciones.nombre_estacion',
            'estaciones.sede',
            'fichapacientes.nombre_completo',
            'fichapacientes.idtipodocumento',
            'fichapacientes.dni',
            'fichapacientes.edad',
            DB::raw('concat(fichapacientes.direccion,"-",graldistritos.nombre,"-",gralprovincias.nombre," ",graldepartamentos.nombre) as direccion'),
            'fichapacientes.telefono',
            'fichapacientes.email',
            'fichapacientes.numero_registro',
            'empresa.descripcion as empresa',
            'fichapacientes.puesto',
            'fichapacientes.anamnesis',
            'fichapacientes.antecedentes_ep',
            'fichapacientes.temperatura',
            'fichapacientes.prueba_serologica'
        )
            ->whereBetween('fecha', [$request->inicio, $request->final])
            ->leftjoin('empresa', 'empresa.idempresa', '=', 'fichapacientes.idempresa')
            ->leftjoin('graldepartamentos', 'graldepartamentos.iddepartamento', '=', 'fichapacientes.id_departamento')
            ->leftjoin('gralprovincias', 'gralprovincias.idprovincia', '=', 'fichapacientes.id_provincia')
            ->leftjoin('graldistritos', 'graldistritos.iddistrito', '=', 'fichapacientes.id_distrito')
            ->leftjoin('estaciones', 'estaciones.idestaciones', '=', 'fichapacientes.id_estacion')
            ->orderBy('fecha', 'ASC')
            ->get();






        //
        //echo ($contador);
        foreach ($fichas as $ficha) {
            $id =  $ficha->idficha_paciente;
            $dni =  $ficha->dni;

            if ($ficha->edad == "") {

                $mediweb_edad = Paciente::select('fechanacimiento')
                    ->where('dni2', 'like', '%' . $dni . '%')->first();
                //$fechanacimiento = date('Y-m-d',$mediweb->fechanacimiento);
                //dd($mediweb->fechanacimiento);
                if ($mediweb_edad != null) {
                    $edad = $this->calculaedad($mediweb_edad->fechanacimiento);
                } else {
                    $edad = '--';
                }
                $ficha->edad = $edad;
            }

            if ($ficha->email == "") {
                $mediweb = Paciente::select('correo')
                    ->where('dni2', 'like', '%' . $dni . '%')->first();

                if (is_null($mediweb)) {
                    $ficha->email = '-';
                } else {
                    $ficha->email = $mediweb->correo;
                }
            }

            if ($ficha->puesto == "") {
                $ficha->puesto = $ficha->puesto_mw;
            }
            if ($ficha->empresa == null) {
                $ficha->empresa = 'SOCIEDAD MINERA CERRO VERDE S.A.A.';
            }
            //dd($ficha);
            //dd($ficha->anamnesis);
            if (is_null($ficha->anamnesis)) {

                // dd($ficha);
                $datosclinicos = DatosClinicos::where('idfichapacientes', $id)
                    ->where(function ($q) {
                        $q->where('tos', 1)
                            ->orWhere('dolor_garganta', 1)
                            ->orWhere('dificultad_respiratoria', 1)
                            ->orWhere('fiebre', 1)
                            ->orWhere('malestar_general', 1)
                            ->orWhere('diarrea', 1)
                            ->orWhere('anosmia_ausegia', 1)
                            ->orwhereNotNull('otros')
                            ->orwhereNotNull('toma_medicamento')
                            ->orWhere('nauseas_vomitos', 1)
                            ->orWhere('congestion_nasal', 1)
                            ->orWhere('cefalea', 1)
                            ->orWhere('irritabilidad_confusion', 1)
                            ->orWhere('falta_aliento', 1);
                    })
                    ->count();

                if ($datosclinicos == 0) {

                    $datosclinicos = "NO";
                } else {
                    $datosclinicosf = DatosClinicos::where('idfichapacientes', $id)
                        ->first();
                    $datosc = array();


                    if ($datosclinicosf->tos == 1)
                        array_push($datosc, 'tos');
                    if ($datosclinicosf->dolor_garganta == 1)
                        array_push($datosc, 'dolor de garganta ');
                    if ($datosclinicosf->dificultad_respiratoria == 1)
                        array_push($datosc, 'dificultad respiratoria');
                    if ($datosclinicosf->fiebre == 1)
                        array_push($datosc, 'fiebre');
                    if ($datosclinicosf->malestar_general == 1)
                        array_push($datosc, 'malestar general');
                    if ($datosclinicosf->diarrea == 1)
                        array_push($datosc, 'diarrea');
                    if ($datosclinicosf->anosmia_ausegia == 1)
                        array_push($datosc, 'anosmia-ausegia');
                    if ($datosclinicosf->cefalea == 1)
                        array_push($datosc, 'cefalea');
                    if ($datosclinicosf->irritabilidad_confusion == 1)
                        array_push($datosc, 'irritabilidad y confusion');
                    if ($datosclinicosf->falta_aliento == 1)
                        array_push($datosc, 'falta de aliento');
                    if ($datosclinicosf->congestion_nasal == 1)
                        array_push($datosc, 'congestion nasal');

                    if (!empty($datosclinicosf->otros)) {
                        array_push($datosc, 'otros:(' . $datosclinicosf->otros . ')');
                    }



                    $result = implode(", ", $datosc);

                    $datosclinicos = $result;
                }


                $ficha->anamnesis = $datosclinicos;
            }
            if (is_null($ficha->antecedentes_ep)) {


                $antecedentesep = AntecedentesEp::where('idfichapacientes', $id)
                    ->where(function ($q) {
                        $q->where('dias_viaje', 1)
                            ->orWhere('contacto_cercano', 1)
                            ->orWhere('conv_covid', 1)
                            ->orwhereNotNull('paises_visitados')
                            ->orwhereNotNull('debilite_sistema');
                    })
                    ->count();

                // dd($antecedentesep);

                if ($antecedentesep == 0) {
                    $antecedentesep = AntecedentesEp::where('idfichapacientes', $id)
                        ->first();

                    if (is_null($antecedentesep)) {
                        $antecedentesep = "NO";
                    } else {
                        $antecedentesep = "NO";
                    }
                } else {
                    $antecedentesepf = AntecedentesEp::where('idfichapacientes', $id)
                        ->first();

                    $debilite_sistema = DatosClinicos::where('idfichapacientes', $id)
                        ->whereNotNull('debilite_sistema')
                        ->first();
                    //dd($antecedentesepf);
                    $datosc = array();
                    if ($antecedentesepf->dias_viaje == 1)
                        array_push($datosc, 'ha viajado en los ultimos 14 dias');
                    if ($antecedentesepf->contacto_cercano == 1)
                        array_push($datosc, 'contacto cercano');
                    if ($antecedentesepf->conv_covid == 1)
                        array_push($datosc, 'conversacion con sospechoso covid 19');
                    if (is_null($antecedentesepf->debilite_sistema)) {
                    } else {
                        array_push($datosc, 'Condicion que debilite sistema:' . $antecedentesepf->debilite_sistema);
                    }
                    if (is_null($debilite_sistema)) {
                    } else {
                        array_push($datosc, 'Condición que debilite el sistema:' . $debilite_sistema->paises_visitados);
                    }
                    if (!empty($antecedentesepf->paises_visitados)) {
                        array_push($datosc, 'otros:(' . $antecedentesepf->paises_visitados . ')');
                    }

                    $result = implode(", ", $datosc);

                    $antecedentesep = $result;
                }


                $ficha->antecedentes_ep = $antecedentesep;
            }


            if (is_null($ficha->temperatura)) {
                $temperatura = Temperatura::where('idfichapacientes', $id)
                    ->first();
                //dd($temperatura);
                if (is_null($temperatura)) {
                    $ficha->temperatura = '36.6';
                } else {
                    $ficha->temperatura = $temperatura->valortemp;
                }
            }



            if (is_null($ficha->prueba_serologica)) {
                $pruebaseriologica = PruebaSerologica::where('idfichapacientes', $id)
                    ->orderBy('created_at', 'DESC')
                    ->first();

                //print_r($pruebaseriologica->p1_react1gm);

                if (is_null($pruebaseriologica)) {
                    $pruebaseriologica = "NEGATIVO";
                } else {
                    if ($pruebaseriologica->p1_react1gm == '0' &&  $pruebaseriologica->p1_reactigg == '0' && $pruebaseriologica->p1_reactigm_igg == '0') {
                        //dd(pruebaseriologica);
                        $pruebaseriologica = "NEGATIVO";
                    } else {
                        if ($pruebaseriologica->p1_react1gm == null &&  $pruebaseriologica->p1_reactigg == null && $pruebaseriologica->p1_reactigm_igg == null) {
                            $pruebaseriologica = "NEGATIVO";
                        } else {

                            $pruebaseriologica = "POSITIVO";
                        }
                    }

                    $ficha->prueba_serologica = $pruebaseriologica;

                    $pruebaseriologicaf = PruebaSerologica::where('idfichapacientes', $id)
                        ->orderBy('created_at', 'DESC')
                        ->first();


                    $datosc = array();
                    if ($pruebaseriologicaf->ps_llamada_113 == 1)
                        array_push($datosc, 'llamada al 113');
                    if ($pruebaseriologicaf->ps_contactocasocon == 1)
                        array_push($datosc, 'contacto caso conocido');
                    if ($pruebaseriologicaf->ps_contactocasosos == 1)
                        array_push($datosc, 'contacto casos sopechosp');
                    if ($pruebaseriologicaf->ps_personaext == 1)
                        array_push($datosc, 'personal externo');
                    if ($pruebaseriologicaf->ps_personalsalud == 1)
                        array_push($datosc, 'personal salud');
                    if (!empty($pruebaseriologicaf->ps_otro)) {
                        array_push($datosc, 'otros:(' . $pruebaseriologicaf->ps_otro . ')');
                    }

                    $result = implode(",", $datosc);

                    $ficha->procedencia = $result;

                    if ($pruebaseriologicaf->ccs == 0) {
                        $ficha->gravedad = '';
                    } else {
                        $ficha->gravedad = $pruebaseriologicaf->ccs;
                    }
                    $ficha->condicion_riesgo_detalle = $pruebaseriologicaf->condicion_riesgo_detalle;
                }
            }





            //return Excel::download(new MttRegistrationsExport($request->id), 'MttRegistrations.xlsx');

        }
        return Excel::download(new SaludExport($fichas), 'examenes_salud.xlsx');
    }

    public function exportNuevoSalud(Request $request)
    {


        $fechaActual2 = '2020-07-13';
        $fechaActual = date('Y-m-d');




        $ficha = FichaPaciente::whereBetween('fecha', [$request->inicio, $request->final])
            //->where('fichapacientes.nombre_completo','like','%edwin quispe%')
            //->with("Empresa")
            ->with('Empresa:idempresa,descripcion,nombrecomercial')
            ->with('Distrito.Provincia.Departamento')

            /*->whereHas(["Estacion.Sede" => function ($q) use ($sede) {
            $q->where('sedes.idsedes',$sede);
            //$q->where('some other field', $someId);
        }])*/
            ->with("Estacion.Sede")
            ->with("DatosClinicos")
            ->with("AntecedentesEp")
            ->with(['PruebaSerologica' => function ($q) {
                $q->orderBy('pruebaserologicas.created_at', 'DESC');
            }])
            ->with(['Temperatura' => function ($q) {
                $q->latest()->first();
            }])

            ->orderBy('fichapacientes.created_at', 'ASC')
            ->get();


        foreach ($ficha as $fichas) {
            $id =  $fichas->idficha_paciente;
            $dni =  $fichas->dni;
            //dd($dni);



        }

        return Excel::download(new NuevoSaludExport($ficha), 'invoices.xlsx');
    }

    public function exportNuevoSaludSo(Request $request)
    {


        $fechaActual2 = '2020-07-13';
        $fechaActual = date('Y-m-d');
        $sede = 5;




        $ficha = FichaPaciente::whereBetween('fecha', [$request->inicio, $request->final])
            ->where('fichapacientes.id_estacion', 21)
            ->orWhere('fichapacientes.id_estacion', 24)
            //->with("Empresa")
            ->with('Empresa:idempresa,descripcion,nombrecomercial')
            ->with('Distrito.Provincia.Departamento')

            /*->whereHas(["Estacion.Sede" => function ($q) use ($sede) {
                $q->where('sedes.idsedes', $sede);
                //$q->where('some other field', $someId);
            }])*/
            ->with("Estacion.Sede")
            ->with("DatosClinicos")
            ->with("AntecedentesEp")
            ->with(['PruebaSerologica' => function ($q) {
                $q->orderBy('pruebaserologicas.created_at', 'DESC');
            }])
            ->with(['Temperatura' => function ($q) {
                $q->latest()->first();
            }])

            ->orderBy('fichapacientes.created_at', 'ASC')
            ->get();

        return Excel::download(new NuevoSaludExport($ficha), 'invoices.xlsx');
    }


    public function indexPendientes(Request $request)
    {
        // if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;

        if ($buscar == '') {
            $fichas = FichaPaciente::where('estado', 0)->paginate(10);


            foreach ($fichas as $ficha) {
                $id =  $ficha->idficha_paciente;

                if ($ficha->puesto == "") {
                    $ficha->puesto = $ficha->puesto_mw;
                }

                $datosclinicos = DatosClinicos::where('idfichapacientes', $id)
                    ->where(function ($q) {
                        $q->where('tos', 1)
                            ->orWhere('dolor_garganta', 1)
                            ->orWhere('dificultad_respiratoria', 1)
                            ->orWhere('fiebre', 1)
                            ->orWhere('malestar_general', 1)
                            ->orWhere('diarrea', 1)
                            ->orWhere('anosmia_ausegia', 1)
                            ->orwhereNotNull('otros')
                            ->orwhereNotNull('toma_medicamento')
                            ->orwhereNotNull('debilite_sistema')
                            ->orWhere('nauseas_vomitos', 1)
                            ->orWhere('congestion_nasal', 1)
                            ->orWhere('cefalea', 1)
                            ->orWhere('irritabilidad_confusion', 1)
                            ->orWhere('falta_aliento', 1);
                    })
                    ->count();

                if ($datosclinicos == 0) {
                    $datosclinicos = DatosClinicos::where('idfichapacientes', $id)
                        ->first();

                    if (is_null($datosclinicos)) {
                        $datosclinicos = "";
                    } else {
                        $datosclinicos = "NO";
                    }
                } else {
                    $datosclinicos = "SI";
                }


                $ficha->anamnesis = $datosclinicos;


                $antecedentesep = AntecedentesEp::where('idfichapacientes', $id)
                    ->where(function ($q) {
                        $q->where('dias_viaje', 1)
                            ->orWhere('contacto_cercano', 1)
                            ->orWhere('conv_covid', 1)
                            ->orwhereNotNull('paises_visitados');
                    })
                    ->count();


                if ($antecedentesep == 0) {
                    $antecedentesep = AntecedentesEp::where('idfichapacientes', $id)
                        ->first();

                    if (is_null($antecedentesep)) {
                        $antecedentesep = "";
                    } else {
                        $antecedentesep = "NO";
                    }
                } else {
                    $antecedentesep = "SI";
                }


                $ficha->antecedentesep = $antecedentesep;


                $pruebaseriologica = PruebaSerologica::where('idfichapacientes', $id)
                    ->where(function ($q) {
                        $q->where('p1_react1gm', 1)
                            ->orWhere('p1_reactigg', 1)
                            ->orWhere('p1_reactigm_igg', 1);
                    })
                    ->count();

                if ($pruebaseriologica == 0) {
                    $pruebaseriologica = PruebaSerologica::where('idfichapacientes', $id)
                        ->first();

                    if (is_null($pruebaseriologica)) {
                        $pruebaseriologica = "";
                    } else {
                        $pruebaseriologica = "NO";
                    }
                } else {
                    $pruebaseriologica = "SI";
                }




                $ficha->pruebaSerologica = $pruebaseriologica;

                $temperatura = Temperatura::where('idfichapacientes', $id)
                    ->first();
                //dd($temperatura);
                if (is_null($temperatura)) {
                } else {
                    $ficha->temperatura = $temperatura->valortemp;
                }
            }
        } else {

            if ($criterio == 'nombre_completo') {

                $fichas = FichaPaciente::where(DB::raw($criterio), 'like', '%' . $buscar . '%')
                    ->orderBy('idficha_paciente', 'desc')
                    ->paginate(10);
            }
            if ($criterio == 'dni') {


                $fichas = FichaPaciente::where($criterio, 'like', '%' . $buscar . '%')->orderBy('idficha_paciente', 'desc')->paginate(10);
            }
            if ($criterio == 'registro') {


                $fichas = Fichapaciente::where($criterio, 'like', '%' . $buscar . '%')->orderBy('idficha_paciente', 'desc')->paginate(10);
            }
        }



        foreach ($fichas as $ficha) {
            $id =  $ficha->idficha_paciente;

            if ($ficha->puesto == "") {
                $ficha->puesto = $ficha->puesto_mw;
            }

            $datosclinicos = DatosClinicos::where('idfichapacientes', $id)
                ->where(function ($q) {
                    $q->where('tos', 1)
                        ->orWhere('dolor_garganta', 1)
                        ->orWhere('dificultad_respiratoria', 1)
                        ->orWhere('fiebre', 1)
                        ->orWhere('malestar_general', 1)
                        ->orWhere('diarrea', 1)
                        ->orWhere('anosmia_ausegia', 1)
                        ->orwhereNotNull('otros')
                        ->orwhereNotNull('toma_medicamento')
                        ->orwhereNotNull('debilite_sistema')
                        ->orWhere('nauseas_vomitos', 1)
                        ->orWhere('congestion_nasal', 1)
                        ->orWhere('cefalea', 1)
                        ->orWhere('irritabilidad_confusion', 1)
                        ->orWhere('falta_aliento', 1);
                })
                ->count();

            if ($datosclinicos == 0) {
                $datosclinicos = DatosClinicos::where('idfichapacientes', $id)
                    ->first();

                if (is_null($datosclinicos)) {
                    $datosclinicos = "";
                } else {
                    $datosclinicos = "NO";
                }
            } else {
                $datosclinicos = "SI";
            }


            $ficha->anamnesis = $datosclinicos;


            $antecedentesep = AntecedentesEp::where('idfichapacientes', $id)
                ->where(function ($q) {
                    $q->where('dias_viaje', 1)
                        ->orWhere('contacto_cercano', 1)
                        ->orWhere('conv_covid', 1)
                        ->orwhereNotNull('paises_visitados');
                })
                ->count();


            if ($antecedentesep == 0) {
                $antecedentesep = AntecedentesEp::where('idfichapacientes', $id)
                    ->first();

                if (is_null($antecedentesep)) {
                    $antecedentesep = "";
                } else {
                    $antecedentesep = "NO";
                }
            } else {
                $antecedentesep = "SI";
            }


            $ficha->antecedentesep = $antecedentesep;


            $pruebaseriologica = PruebaSerologica::where('idfichapacientes', $id)
                ->where(function ($q) {
                    $q->where('p1_react1gm', 1)
                        ->orWhere('p1_reactigg', 1)
                        ->orWhere('p1_reactigm_igg', 1);
                })
                ->count();

            if ($pruebaseriologica == 0) {
                $pruebaseriologica = PruebaSerologica::where('idfichapacientes', $id)
                    ->first();

                if (is_null($pruebaseriologica)) {
                    $pruebaseriologica = "";
                } else {
                    $pruebaseriologica = "NO";
                }
            } else {
                $pruebaseriologica = "SI";
            }




            $ficha->pruebaSerologica = $pruebaseriologica;

            $temperatura = Temperatura::where('idfichapacientes', $id)
                ->first();
            //dd($temperatura);
            if (is_null($temperatura)) {
            } else {
                $ficha->temperatura = $temperatura->valortemp;
            }
        }







        return [
            'pagination' => [
                'total'        => $fichas->total(),
                'current_page' => $fichas->currentPage(),
                'per_page'     => $fichas->perPage(),
                'last_page'    => $fichas->lastPage(),
                'from'         => $fichas->firstItem(),
                'to'           => $fichas->lastItem(),
            ],
            'categorias' => $fichas
        ];
    }

    public function indexSintomaticos(Request $request)
    {
        // if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;

        if ($buscar == '') {
            $fichas = FichaPaciente::paginate(10);


            foreach ($fichas as $ficha) {
                $id =  $ficha->idficha_paciente;

                if ($ficha->puesto == "") {
                    $ficha->puesto = $ficha->puesto_mw;
                }

                $datosclinicos = DatosClinicos::where('idfichapacientes', $id)
                    ->where(function ($q) {
                        $q->where('tos', 1)
                            ->orWhere('dolor_garganta', 1)
                            ->orWhere('dificultad_respiratoria', 1)
                            ->orWhere('fiebre', 1)
                            ->orWhere('malestar_general', 1)
                            ->orWhere('diarrea', 1)
                            ->orWhere('anosmia_ausegia', 1)
                            ->orwhereNotNull('otros')
                            ->orwhereNotNull('toma_medicamento')
                            ->orwhereNotNull('debilite_sistema')
                            ->orWhere('nauseas_vomitos', 1)
                            ->orWhere('congestion_nasal', 1)
                            ->orWhere('cefalea', 1)
                            ->orWhere('irritabilidad_confusion', 1)
                            ->orWhere('falta_aliento', 1);
                    })
                    ->count();

                if ($datosclinicos == 0) {
                    $datosclinicos = DatosClinicos::where('idfichapacientes', $id)
                        ->first();

                    if (is_null($datosclinicos)) {
                        $datosclinicos = "";
                    } else {
                        $datosclinicos = "NO";
                    }
                } else {
                    $datosclinicos = "SI";
                }


                $ficha->anamnesis = $datosclinicos;


                $antecedentesep = AntecedentesEp::where('idfichapacientes', $id)
                    ->where(function ($q) {
                        $q->where('dias_viaje', 1)
                            ->orWhere('contacto_cercano', 1)
                            ->orWhere('conv_covid', 1)
                            ->orwhereNotNull('paises_visitados');
                    })
                    ->count();


                if ($antecedentesep == 0) {
                    $antecedentesep = AntecedentesEp::where('idfichapacientes', $id)
                        ->first();

                    if (is_null($antecedentesep)) {
                        $antecedentesep = "";
                    } else {
                        $antecedentesep = "NO";
                    }
                } else {
                    $antecedentesep = "SI";
                }


                $ficha->antecedentesep = $antecedentesep;


                $pruebaseriologica = PruebaSerologica::where('idfichapacientes', $id)
                    ->where(function ($q) {
                        $q->where('p1_react1gm', 1)
                            ->orWhere('p1_reactigg', 1)
                            ->orWhere('p1_reactigm_igg', 1);
                    })
                    ->count();

                if ($pruebaseriologica == 0) {
                    $pruebaseriologica = PruebaSerologica::where('idfichapacientes', $id)
                        ->first();

                    if (is_null($pruebaseriologica)) {
                        $pruebaseriologica = "";
                    } else {
                        $pruebaseriologica = "NO";
                    }
                } else {
                    $pruebaseriologica = "SI";
                }




                $ficha->pruebaSerologica = $pruebaseriologica;

                $temperatura = Temperatura::where('idfichapacientes', $id)
                    ->first();
                //dd($temperatura);
                if (is_null($temperatura)) {
                } else {
                    $ficha->temperatura = $temperatura->valortemp;
                }
            }
        } else {

            if ($criterio == 'nombre_completo') {

                $fichas = FichaPaciente::where(DB::raw($criterio), 'like', '%' . $buscar . '%')
                    ->orderBy('idficha_paciente', 'desc')
                    ->paginate(10);
            }
            if ($criterio == 'dni') {


                $fichas = FichaPaciente::where($criterio, 'like', '%' . $buscar . '%')->orderBy('idficha_paciente', 'desc')->paginate(10);
            }
            if ($criterio == 'registro') {


                $fichas = Fichapaciente::where($criterio, 'like', '%' . $buscar . '%')->orderBy('idficha_paciente', 'desc')->paginate(10);
            }
        }



        foreach ($fichas as $ficha) {
            $id =  $ficha->idficha_paciente;

            if ($ficha->puesto == "") {
                $ficha->puesto = $ficha->puesto_mw;
            }

            $datosclinicos = DatosClinicos::where('idfichapacientes', $id)
                ->where(function ($q) {
                    $q->where('tos', 1)
                        ->orWhere('dolor_garganta', 1)
                        ->orWhere('dificultad_respiratoria', 1)
                        ->orWhere('fiebre', 1)
                        ->orWhere('malestar_general', 1)
                        ->orWhere('diarrea', 1)
                        ->orWhere('anosmia_ausegia', 1)
                        ->orwhereNotNull('otros')
                        ->orwhereNotNull('toma_medicamento')
                        ->orwhereNotNull('debilite_sistema')
                        ->orWhere('nauseas_vomitos', 1)
                        ->orWhere('congestion_nasal', 1)
                        ->orWhere('cefalea', 1)
                        ->orWhere('irritabilidad_confusion', 1)
                        ->orWhere('falta_aliento', 1);
                })
                ->count();

            if ($datosclinicos == 0) {
                $datosclinicos = DatosClinicos::where('idfichapacientes', $id)
                    ->first();

                if (is_null($datosclinicos)) {
                    $datosclinicos = "";
                } else {
                    $datosclinicos = "NO";
                }
            } else {
                $datosclinicos = "SI";
            }


            $ficha->anamnesis = $datosclinicos;


            $antecedentesep = AntecedentesEp::where('idfichapacientes', $id)
                ->where(function ($q) {
                    $q->where('dias_viaje', 1)
                        ->orWhere('contacto_cercano', 1)
                        ->orWhere('conv_covid', 1)
                        ->orwhereNotNull('paises_visitados');
                })
                ->count();


            if ($antecedentesep == 0) {
                $antecedentesep = AntecedentesEp::where('idfichapacientes', $id)
                    ->first();

                if (is_null($antecedentesep)) {
                    $antecedentesep = "";
                } else {
                    $antecedentesep = "NO";
                }
            } else {
                $antecedentesep = "SI";
            }


            $ficha->antecedentesep = $antecedentesep;


            $pruebaseriologica = PruebaSerologica::where('idfichapacientes', $id)
                ->where(function ($q) {
                    $q->where('p1_react1gm', 1)
                        ->orWhere('p1_reactigg', 1)
                        ->orWhere('p1_reactigm_igg', 1);
                })
                ->count();

            if ($pruebaseriologica == 0) {
                $pruebaseriologica = PruebaSerologica::where('idfichapacientes', $id)
                    ->first();

                if (is_null($pruebaseriologica)) {
                    $pruebaseriologica = "";
                } else {
                    $pruebaseriologica = "NO";
                }
            } else {
                $pruebaseriologica = "SI";
            }




            $ficha->pruebaSerologica = $pruebaseriologica;

            $temperatura = Temperatura::where('idfichapacientes', $id)
                ->first();
            //dd($temperatura);
            if (is_null($temperatura)) {
            } else {
                $ficha->temperatura = $temperatura->valortemp;
            }
        }

        return [
            'pagination' => [
                'total'        => $fichas->total(),
                'current_page' => $fichas->currentPage(),
                'per_page'     => $fichas->perPage(),
                'last_page'    => $fichas->lastPage(),
                'from'         => $fichas->firstItem(),
                'to'           => $fichas->lastItem(),
            ],
            'categorias' => $fichas
        ];
    }
    public function indexPasadas(Request $request)
    {
        // if (!$request->ajax()) return redirect('/');
        $buscar = $request->buscar;
        $criterio = $request->criterio;

        $fechaActual = date('Y-m-d');

        if ($buscar == '') {




            $fechaActual2 = '2020-07-07';
            $fechaActual = date('Y-m-d');

            $fichas = FichaPaciente::where('fichapacientes.fecha', $fechaActual)
                //->where('fichapacientes.nombre_completo','like','%edwin quispe%')
                //->with("Empresa")
                ->with('Empresa:idempresa,descripcion,nombrecomercial')
                /*->whereHas(
                    'Estacion', function ($q) use ($estacion){
                        $q->where('id_estacion', $estacion);
                    }
                )*/
                /*->whereHas(["Estacion.Sede" => function ($q) use ($sede) {
                    $q->where('sedes.idsedes',$sede);
                    //$q->where('some other field', $someId);
                }])*/
                ->with("Estacion.Sede")
                ->with("DatosClinicos")
                ->with("AntecedentesEp")
                ->with("PruebaSerologica")
                ->with(['DeclaracionJurada' => function ($q) {
                    $q->orderBy('declaracionesjuradas.created_at', 'DESC');
                }])
                ->with(['ConsentimientoInformado' => function ($q) {
                    $q->orderBy('consentimientoinformados.created_at', 'DESC');
                }])
                ->with(['AnexoTres' => function ($q) {
                    $q->orderBy('anexotres.created_at', 'DESC');
                }])
                ->with("Temperatura")
                ->orderBy('fichapacientes.created_at', 'ASC')
                ->paginate(15);
        } else {

            $fechaActual2 = '2020-07-07';
            $fechaActual = date('Y-m-d');

            $fichas = FichaPaciente::where(DB::raw($criterio), 'like', '%' . $buscar . '%')
                ->with('Empresa:idempresa,descripcion,nombrecomercial')
                ->with("Estacion.Sede")
                ->with("DatosClinicos")
                ->with("AntecedentesEp")
                ->with("PruebaSerologica")
                ->with(['DeclaracionJurada' => function ($q) {
                    $q->orderBy('declaracionesjuradas.created_at', 'DESC');
                }])
                ->with(['ConsentimientoInformado' => function ($q) {
                    $q->orderBy('consentimientoinformados.created_at', 'DESC');
                }])
                ->with(['AnexoTres' => function ($q) {
                    $q->orderBy('anexotres.created_at', 'DESC');
                }])
                ->with("Temperatura")
                ->orderBy('fichapacientes.created_at', 'ASC')
                ->paginate(15);
        }

        return [
            'pagination' => [
                'total'        => $fichas->total(),
                'current_page' => $fichas->currentPage(),
                'per_page'     => $fichas->perPage(),
                'last_page'    => $fichas->lastPage(),
                'from'         => $fichas->firstItem(),
                'to'           => $fichas->lastItem(),
            ],
            'categorias' => $fichas
        ];
    }

    public function buscar(Request $request)
    {
        // if (!$request->ajax()) return redirect('/');
        //Schema::connection('mysql2')

        // echo $request->criterio;
        if ($request->criterio == "dni") {

            $mediweb = Paciente::select('idpaciente', 'nombres', 'apellido_paterno', 'apellido_materno', 'dni2 as dni')
                ->where('dni2', 'like', '%' . $request->dni . '%')->get();

            if ($mediweb->count() > 0) {
                foreach ($mediweb as $exam) {
                    $id =  $exam->idpaciente;

                    $comprobante = Comprobante::select('idcomprobante')
                        ->where('estado', "=", '1')
                        ->where('idpaciente', '=', $id)
                        ->orderby('fecha', 'DESC')->take(1)->get();


                    $exam->comprobante = $comprobante;
                }
                foreach ($mediweb as $exam) {
                    foreach ($exam->comprobante as $exam2) {
                        $id =  $exam2->idcomprobante;

                        $comprobante = Comprobante::select('empresa.descripcion as empresa', 'comprobante.area as area_mw', 'comprobante.puesto as puesto_mw', 'empresa.idempresa as idempresa')
                            ->leftjoin('empresa', 'empresa.idempresa', '=', 'comprobante.idsubcontrata')
                            ->where('comprobante.idcomprobante', '=', $id)->get();

                        //$related = new Collection();
                        //$merged =   $collection->merge($item);

                        if ($comprobante->count() > 0) {
                            foreach ($comprobante as $valor) {



                                $exam->empresa = $valor['empresa'];
                                $exam->idempresa = $valor['idempresa'];
                                $exam->area_mw = $valor['area_mw'];
                                $exam->puesto_mw = $valor['puesto_mw'];
                            }
                        } else
                            $exam->empresa = 'SOCIEDAD MINERA CERRO VERDE';
                    }
                }
            } else {
                $mediweb = PersonaZap::select('nombres_apellidos as nombres', 'registro as registro', 'num_documento as dni')
                    ->where('num_documento', 'like', '%' . $request->dni . '%')->get();
                foreach ($mediweb as $empresa) {
                    $empresa->empresa = 'SOCIEDAD MINERA CERRO VERDE';
                }
            }
        }


        if ($request->criterio == "nombres") {


            $criterio = 'concat(nombres," ",apellido_paterno," ",apellido_materno)';

            $mediweb = Paciente::select('idpaciente', 'nombres', 'apellido_paterno', 'apellido_materno', 'dni2 as dni')
                ->where(DB::raw($criterio), 'like', '%' . $request->dni . '%')
                ->get();

            /* $zap = PersonaZap::select('registro','puesto','gerencia','area')
    ->where('dni','like', '%'.$request->dni.'%')->get();*/


            foreach ($mediweb as $exam) {
                $id =  $exam->idpaciente;

                $comprobante = Comprobante::select('idcomprobante')
                    ->where('estado', "=", '1')
                    ->where('idpaciente', '=', $id)
                    ->orderby('fecha', 'DESC')->take(1)->get();

                //$related = new Collection();
                //$merged =   $collection->merge($item);

                $exam->comprobante = $comprobante;
            }

            foreach ($mediweb as $exam) {
                foreach ($exam->comprobante as $exam2) {
                    $id =  $exam2->idcomprobante;

                    $comprobante = Comprobante::select('empresa.descripcion as empresa', 'empresa.idempresa as idempresa', 'comprobante.area as area_mw', 'comprobante.puesto as puesto_mw')
                        ->leftjoin('empresa', 'empresa.idempresa', '=', 'comprobante.idsubcontrata')
                        ->where('comprobante.idcomprobante', '=', $id)->get();

                    //$related = new Collection();
                    //$merged =   $collection->merge($item);

                    if ($comprobante->count() > 0) {
                        foreach ($comprobante as $valor) {



                            $exam->empresa = $valor['empresa'];
                            $exam->idempresa = $valor['idempresa'];
                            $exam->area_mw = $valor['area_mw'];
                            $exam->puesto_mw = $valor['puesto_mw'];
                        }
                    } else
                        $exam->empresa = 'SOCIEDAD MINERA CERRO VERDE';
                }
            }
        }

        if ($request->criterio == "registro") {


            /*$mediweb= Paciente::select('idpaciente','nombres','apellido_paterno','apellido_materno','dni2 as dni')
       ->where('dni2','like', '%'.$request->dni.'%')->get();*/

            $mediweb = PersonaZap::select('nombres_apellidos as nombres', 'registro as registro', 'num_documento as dni')
                ->where('registro', 'like', '%' . $request->dni . '%')->get();


            foreach ($mediweb as $exam) {
                $id =  $exam->idpaciente;

                $comprobante = Comprobante::select('idcomprobante')
                    ->where('estado', "=", '1')
                    ->where('idpaciente', '=', $id)
                    ->orderby('fecha', 'DESC')->take(1)->get();

                //$related = new Collection();
                //$merged =   $collection->merge($item);

                $exam->comprobante = $comprobante;
                $exam->empresa = 'SOCIEDAD MINERA CERRO VERDE';
            }
        }




        return ['paciente' => $mediweb];
    }



    public function buscarDatosAdicionales(Request $request)
    {
        // if (!$request->ajax()) return redirect('/');
        //Schema::connection('mysql2')

        /* $zap = PersonaZap::select('registro','puesto','gerencia','area')
       ->where('dni','like', '%'.$request->dni.'%')->get();*/



        $mediweb = Paciente::select(
            'paciente.nombres',
            'paciente.apellido_materno',
            'paciente.apellido_paterno',
            'paciente.direccion',
            'paciente.telefono',
            'graldistritos.nombre as distrito',
            'gralprovincias.nombre as provincia',
            'graldepartamentos.nombre as departamento',
            'graldistritos.iddistrito as distrito_id',
            'gralprovincias.idprovincia as provincia_id',
            'graldepartamentos.iddepartamento as departamento_id',
            'paciente.dni2 as dni',
            'paciente.idtipodocumento as tipodocumento',
            'paciente.correo as email'
        )
            ->leftjoin('graldistritos', 'graldistritos.iddistrito', '=', 'paciente.iddistrito')
            ->leftjoin('gralprovincias', 'gralprovincias.idprovincia', '=', 'paciente.idprovincia')
            ->leftjoin('graldepartamentos', 'graldepartamentos.iddepartamento', '=', 'paciente.iddepartamento')
            ->where('paciente.dni2', 'like', '%' . $request->dni . '%')->get();

        foreach ($mediweb as $zap) {



            $Paciente = PersonaZap::select('puesto', 'area', 'gerencia', 'registro')
                ->where('num_documento', '=', $request->dni)->get();
            foreach ($Paciente as $datos_a) {

                $zap->puesto = $datos_a['puesto'];
                $zap->area = $datos_a['area'];
                $zap->gerencia = $datos_a['gerencia'];
                $zap->registro = $datos_a['registro'];
            }
        }

        //dd($categorias[0]);
        //$encodedData = json_encode($categorias, JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_IGNORE);
        //dd($categorias);


        //dd($mediweb);







        return ['paciente' => $mediweb];
        //$categorias = DB::connection('mysql2')->where('dni2','=','72815630')
        //     ->get();
        //print($categorias[0]['attributes']);

    }





    public function buscarExisteRegistro(Request $request)
    {
        $fechaActual = date('Y-m-d');
        //$fechaActual = '2020-10-30';
        $count = FichaPaciente::where('dni', '=', $request->dni)->where('fecha', '=', $fechaActual)->count();

        return ['existeficha' => $count, 'fecha' => $fechaActual, 'dni' => $request->dni];

    }


    public function buscarParaPrueba(Request $request)
    {
        // if (!$request->ajax()) return redirect('/');
        //Schema::connection('mysql2')

        /* $zap = PersonaZap::select('registro','puesto','gerencia','area')
       ->where('dni','like', '%'.$request->dni.'%')->get();*/
        $comprobante = Comprobante::select(
            'paciente.nombres',
            'paciente.apellido_materno',
            'paciente.apellido_paterno',
            'paciente.correo',
            'paciente.direccion',
            'paciente.telefono',
            'graldistritos.nombre as distrito',
            'gralprovincias.nombre as provincia',
            'graldepartamentos.nombre as departamento',
            'paciente.dni2 as dni'
        )
            ->leftjoin('graldistritos', 'graldistritos.iddistrito', '=', 'paciente.iddistrito')
            ->leftjoin('gralprovincias', 'gralprovincias.idprovincia', '=', 'paciente.idprovincia')
            ->leftjoin('graldepartamentos', 'graldepartamentos.iddepartamento', '=', 'paciente.iddepartamento')
            ->where('paciente.dni2', 'like', '%' . $request->dni . '%')->get();


        $mediweb = Paciente::select(
            'paciente.nombres',
            'paciente.apellido_materno',
            'paciente.apellido_paterno',
            'paciente.correo',
            'paciente.direccion',
            'paciente.telefono',
            'graldistritos.nombre as distrito',
            'gralprovincias.nombre as provincia',
            'graldepartamentos.nombre as departamento',
            'paciente.dni2 as dni'
        )
            ->leftjoin('graldistritos', 'graldistritos.iddistrito', '=', 'paciente.iddistrito')
            ->leftjoin('gralprovincias', 'gralprovincias.idprovincia', '=', 'paciente.idprovincia')
            ->leftjoin('graldepartamentos', 'graldepartamentos.iddepartamento', '=', 'paciente.iddepartamento')
            ->where('paciente.dni2', 'like', '%' . $request->dni . '%')->get();

        $query = DB::table('database1.table1 as dt1')->leftjoin('database2.table2 as dt2', 'dt2.ID', '=', 'dt1.ID');
        $output = $query->select(['dt1.*', 'dt2.*'])->get();

        //dd($categorias[0]);
        //$encodedData = json_encode($categorias, JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_IGNORE);
        //dd($categorias);




        return ['paciente' => $mediweb];
        //$categorias = DB::connection('mysql2')->where('dni2','=','72815630')
        //     ->get();
        //print($categorias[0]['attributes']);

    }



    public function ListarDepartamento(Request $request)
    {

        $dep = DepartamentoPeru::all();
        return ['departamento' => $dep];
    }
    public function ListarProvincia(Request $request)
    {

        $prov = Provincia::all();
        return ['provincia' => $prov];
    }
    public function ListarDistrito(Request $request)
    {

        $dist = Distrito::all();
        return ['distrito' => $dist];
    }
    public function ListarEmpresa(Request $request)
    {

        $empresa = Empresa::where('estado', '=', '1')->get();
        return ['empresa' => $empresa];
    }

    public function ListarPaciente(Request $request)
    {

        $categorias = Paciente::select(DB::raw('concat(paciente.nombres," ",paciente.apellidos) as nombre'))
            ->get();
        return ['paciente' => $categorias];
    }

    public function reporte(Request $request)
    {
        // if (!$request->ajax()) return redirect('/');
        //Schema::connection('mysql2')
        $categorias = ExamenOcu::select('examenocu.idexamenocu', 'examenocu.descripcion')
            ->where('examenocu.codigo', 'LIKE', '%LAB%')
            ->get();
        //dd($categorias[0]);
        //$encodedData = json_encode($categorias, JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_IGNORE);
        //dd($categorias);

        foreach ($categorias as $exam) {
            $id =  $exam->idexamenocu;

            $comprobante = Comprobante::where('estado', "=", '1')
                ->whereBetween('fecha', ['2019-03-01', '2020-03-31'])
                ->where(function ($query) use ($id) {
                    $query->where('servicio_atendidos', 'like', '%,' . $id . ',%')
                        ->orwhere('servicio_atendidos', 'like', $id . ',%')
                        ->orwhere('servicio_atendidos', 'like', '%,' . $id);
                })
                ->count();


            echo '<br>{ <br>
            "network": "' . $exam->descripcion . '",<br>
            "MAU":"' . $comprobante . '" <br>
        },<br>';
        }




        // return ['paciente' => $categorias];
        //$categorias = DB::connection('mysql2')->where('dni2','=','72815630')
        //     ->get();
        //print($categorias[0]['attributes']);

    }

    public function selectSede(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $categorias = Sede::where('condicion', '=', '1')
            ->select('id', 'descripcion')->orderBy('descripcion', 'asc')->get();
        return ['categorias' => $categorias];
    }

    public function listarPdf()
    {
        $categorias = Categoria::select('nombre', 'descripcion', 'condicion')->orderBy('nombre', 'asc')->get();
        $cont = Categoria::count();

        $pdf = \PDF::loadView('pdf.categoriaspdf', ['categorias' => $categorias, 'cont' => $cont])->setPaper('a4', 'portrait');
        return $pdf->download('categorias.pdf');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /*public function store(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $ubicacion = new Vecino();
        $ubicacion->dni = $request->dni;
        $ubicacion->nombres = $request->nombres;
        $ubicacion->apellido_paterno = $request->apellido_paterno;
        $ubicacion->apellido_materno = $request->apellido_materno;
        $ubicacion->condicion = '1';
        $ubicacion->save();
    }*/


    public function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        } else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);

            if ($unit == "K") {
                return ($miles * 1.609344);
            } else if ($unit == "N") {
                return ($miles * 0.8684);
            } else {
                return $miles;
            }
        }
    }


    public function iniciales($nombre)
    {
        $nombre = explode(" ", $nombre);
        $letras = "";
        foreach ($nombre as $v) {
            $letras .= "$v[0]";
        }
        return  strtoupper($letras);
    }






    public function CalcularDistancias(Request $request)
    {

        //$this->sendRequest($uri);



        echo $this->distance(-16.413301, -713545799, -16.421043, -71.553317, "K") . " Kilometers<br>";
    }


    public function BuscarFichaPaciente(Request $request)
    {
        //if (!$request->ajax()) return redirect('/');
        $fechaActual = date('Y-m-d');


        $categorias = FichaPaciente::where('dni', '=', $request->dni)
            ->where('fecha', '=', $fechaActual)
            ->first();
        return ['fichapaciente' => $categorias];
    }

    public function BuscarCodigoPs(Request $request)
    {

        $id = $request->id;
        $fichas = FichaPaciente::where('codigo_ps', $id)
            ->orderBy('idficha_paciente', 'desc')
            ->first();

        return ['fichapaciente' => $fichas];
    }


    public function PonerEstacion(Request $request)
    {
        //if (!$request->ajax()) return redirect('/');

        $fechaActual = date('Y-m-d');

        //$ubicacion = new PruebaSerologica();


        $prueba = new PruebaSerologica();
        $prueba->idfichapacientes = $request->id_ficha_paciente;
        $prueba->hash = $request->id_ficha_paciente . $fechaActual;
        $prueba->save();
        return response()->json(['fichacreada' => $prueba]);
    }

    public function CargarEstados(Request $request)
    {
        $id = $request->id;
        $dc = DatosClinicos::where('idfichapacientes', '=', $id)->count();
        $ps_v = PruebaSerologica::where('idfichapacientes', '=', $id)->where('invalido', '=', 0)->count();
        $ps_i = PruebaSerologica::where('idfichapacientes', '=', $id)->where('invalido', '=', 1)->count();
        $ae = AntecedentesEp::where('idfichapacientes', '=', $id)->count();
        $temp = Temperatura::where('idfichapacientes', '=', $id)->count();
        $dj = DeclaracionJurada::where('idfichapacientes', '=', $id)->count();
        $ci = ConsentimientoInformado::where('idfichapacientes', '=', $id)->count();

        return ['dc' => $dc, 'ps_v' => $ps_v, 'ps_i' => $ps_i, 'ae' => $ae, 'temp' => $temp, 'dj' => $dj, 'ci' => $ci];
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $categoria = Vecino::findOrFail($request->id);
        $categoria->dni = $request->dni;
        $categoria->nombres = $request->nombres;
        $categoria->apellido_paterno = $request->apellido_paterno;
        $categoria->apellido_materno = $request->apellido_materno;
        $categoria->condicion = '1';
        $categoria->save();
    }

    public function desactivar(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $categoria = Lote::findOrFail($request->id);
        $categoria->condicion = '0';
        echo ($request->id);
        $categoria->save();
    }

    public function activar(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $categoria = Lote::findOrFail($request->id);
        $categoria->condicion = '1';
        $categoria->save();
    }

    public function selectLote($id)
    {
        //if (!$request->ajax()) return redirect('/');
        $categorias = Lote::where('condicion', '=', '1')
            ->where('idmanzana', '=', $id)
            ->select('id', 'descripcion')->orderBy('descripcion', 'asc')->get();
        return ['lote' => $categorias];
    }
    public function BuscarDNI($dni)
    {
        $consulta = HtmlDomParser::file_get_html('https://eldni.com/buscar-por-dni?dni=' . $dni);

        $datosnombres = array();
        foreach ($consulta->find('td') as $header) {
            $datosnombres[] = $header->plaintext;
        }
        $datos = array(
            0 => $dni,
            1 => $datosnombres[0],
            2 => $datosnombres[1],
            3 => $datosnombres[2],
        );

        return ['reniec' => $datos];
    }
    public function calculaedad($fechanacimiento)
    {
        list($ano, $mes, $dia) = explode("-", $fechanacimiento);
        $ano_diferencia  = date("Y") - $ano;
        $mes_diferencia = date("m") - $mes;
        $dia_diferencia   = date("d") - $dia;
        if ($dia_diferencia < 0 || $mes_diferencia < 0)
            $ano_diferencia--;
        return $ano_diferencia;
    }

    public function imprimirCertificado(Request $request)
    {
        //$pdf = \PDF::loadView('pdf.certificado');
        $ficha = FichaPaciente::where('fichapacientes.idficha_paciente', $request->fichapaciente)
            //$ficha = FichaPaciente::where('fichapacientes.idficha_paciente', '44471')
            //->with("Empresa")
            ->with('Empresa:idempresa,descripcion,nombrecomercial')
            /*->whereHas(["Estacion.Sede" => function ($q) use ($sede) {
                $q->where('sedes.idsedes',$sede);
                //$q->where('some other field', $someId);
            }])*/
            ->with("Estacion.Sede")
            ->with("DatosClinicos")
            ->with("AntecedentesEp")
            //->with("PruebaSerologica")
            ->with(['PruebaSerologica' => function ($q) {
                $q->orderBy('pruebaserologicas.idpruebaserologicas', 'DESC')->take(1);
            }])
            ->with("Temperatura")
            ->orderBy('fichapacientes.created_at', 'DESC')
            ->get();
        //dd($ficha);

        foreach ($ficha as $fichas) {
            $id =  $fichas->idficha_paciente;
            $dni =  $fichas->dni;

            $mediweb = Paciente::select('fechanacimiento')
                ->where('dni2', 'like', '%' . $dni . '%')->first();
            //$fechanacimiento = date('Y-m-d',$mediweb->fechanacimiento);
            //dd($mediweb->fechanacimiento);
            if ($mediweb != null) {
                $edad = $this->calculaedad($mediweb->fechanacimiento);
            } else {
                $edad = '--';
            }
            $fichas->edad = $edad;
            //dd($edad);

        }
        //dd($ficha);
        //print_r($ficha);
        $pdf = \PDF::loadView('pdf.certificado', compact('ficha'));
        // $pdf = \PDF::loadView('pdf.certificado',$ficha);
        return $pdf->stream('certificado.pdf');
    }
    public function buscarDnis(Request $request)
    {
        $from = date('2020-05-01');
        $to = date('2020-07-31');
        $ficha = FichaPaciente::select(
            'fichapacientes.idficha_paciente',
            'fichapacientes.fecha',
            'estaciones.nombre_estacion',
            'estaciones.sede',
            'fichapacientes.nombre_completo',
            'fichapacientes.idtipodocumento',
            'fichapacientes.dni',
            'fichapacientes.puesto',
            'fichapacientes.edad',
            DB::raw('concat(fichapacientes.direccion,"-",graldistritos.nombre,"-",gralprovincias.nombre," ",graldepartamentos.nombre) as direccion'),
            'fichapacientes.telefono',
            'fichapacientes.email',
            'fichapacientes.numero_registro',
            'empresa.descripcion as empresa'
        )
            ->whereBetween('fecha', [$from, $to])
            ->leftjoin('empresa', 'empresa.idempresa', '=', 'fichapacientes.idempresa')
            ->leftjoin('graldepartamentos', 'graldepartamentos.iddepartamento', '=', 'fichapacientes.id_departamento')
            ->leftjoin('gralprovincias', 'gralprovincias.idprovincia', '=', 'fichapacientes.id_provincia')
            ->leftjoin('graldistritos', 'graldistritos.iddistrito', '=', 'fichapacientes.id_distrito')
            ->leftjoin('estaciones', 'estaciones.idestaciones', '=', 'fichapacientes.id_estacion')
            ->orderBy('fichapacientes.created_at', 'DESC')
            ->groupBy('dni')
            ->get();

        foreach ($ficha as $fichas) {
            $id =  $fichas->idficha_paciente;
            $dni =  $fichas->dni;

            $mediweb = Paciente::select('dni2')
                ->where('dni2', 'like', '%' . $dni . '%')->first();
            if ($mediweb != null) {
                $edad = 'SI';
            } else {
                $edad = 'NO';
            }
            $fichas->edad = $edad;

        }
        return Excel::download(new YadiraExport($ficha), 'examenes.xlsx');
    }

    public function buscarPacienteMinsa(Request $request) {

        $dni = $request->dni;

        $validate = Validator::make($request->all(),[
            'dni' => 'required'
        ]);

        if ($validate->fails()) {
            $respuesta = array(
                'code' => 400,
                'status' => 'error',
                'errors' => $validate->errors()
            );
        } else {

            $paciente_mw = Paciente::select('sexo','correo','idtipodocumento','celular','nombres','apellido_paterno','apellido_materno','dni2','direccion','fechanacimiento','empresa','puesto')
                ->where('dni2', $dni)
                ->first();


            if($paciente_mw) {
                $datos_paciente = array(
                    'tipo_documento' => $paciente_mw->idtipodocumento,
                    'numero_documento' => $dni,
                    'nombres' => $paciente_mw->nombres,
                    'apellido_paterno' => $paciente_mw->apellido_paterno,
                    'apellido_materno' => $paciente_mw->apellido_materno,
                    'sexo' => $paciente_mw->sexo,
                    'fecha_nacimiento' => $paciente_mw->fechanacimiento,
                    'direccion' => $paciente_mw->direccion,
                    'celular' => $paciente_mw->celular,
                    'correo' => $paciente_mw->correo,
                );
            } else {
                    $respuesta = Http::get("https://siscovid.minsa.gob.pe/ficha/api/buscar-documento/01/$dni")->json();
                    $paciente_minsa = $respuesta['datos']['data'];
                    if ($paciente_minsa['sexo'] === 1) {
                        $sexo = "M";
                    } else if ($paciente_minsa['sexo'] === 2) {
                        $sexo = "F";
                    } else {
                        $sexo = "";
                    }
                    if(!array_key_exists('celular',$paciente_minsa)) {
                        $paciente_minsa['celular'] = "";
                    }
                    if(!array_key_exists('correo',$paciente_minsa)) {
                        $paciente_minsa['correo'] = "";
                    }

                    $datos_paciente = array(
                        'tipo_documento' => $paciente_minsa['tipo_documento'],
                        'numero_documento' => $dni,
                        'nombres' => $paciente_minsa['nombres'],
                        'apellido_paterno' => $paciente_minsa['apellido_paterno'],
                        'apellido_materno' => $paciente_minsa['apellido_materno'],
                        'sexo' => $sexo,
                        'fecha_nacimiento' => $paciente_minsa['fecha_nacimiento'],
                        'direccion' => $paciente_minsa['direccion'],
                        'celular' => $paciente_minsa['celular'],
                        'correo' => $paciente_minsa['correo']
                    );
            }

            $respuesta = array(
                'code' => 200,
                'status' => 'success',
                'data' => $datos_paciente,
            );
        }
        return response()->json($respuesta, $respuesta['code']);
    }
}
