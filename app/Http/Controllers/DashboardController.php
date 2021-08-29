<?php

namespace App\Http\Controllers;

use App\ExcelNicole;
use App\Exports\ExportReporteComplejo;
use App\Exports\NuevoSupervisorPcrExport;
use KubAT\PhpSimple\HtmlDomParser;
use App\Paciente;
use App\PcrEnvioMunoz;
use App\FichaPaciente;
use App\PruebaSerologica;
use App\FichaInvestigacion;
use App\PacienteIsos;
use App\Estacion;
use App\Sede;

use App\PcrPruebaMolecular;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use SoapClient;
use AjCastro\Searchable\Searchable;
use Maatwebsite\Excel\Facades\Excel;
use Svg\Tag\Rect;

class DashboardController extends Controller
{
    public function tiemposComplejo(Request $request)
    {
        // if (!$request->ajax()) return redirect('/');
        $inicio = $request->fecha1;
        $fin = $request->fecha2;
        //$inicio = '2020-11-17';
        //$fin = '2020-11-17';
        $fechaActual = date('Y-m-d');
        // $fechaActual = '2020-11-26';

        $pacientes  = FichaPaciente::select(
            'fichapacientes.created_at as start',
            DB::raw("CONCAT(sede.abreviacion,' ',estacion.nombre_estacion) AS task")
        )
            ->join('estaciones as estacion', 'estacion.idestaciones', '=', 'fichapacientes.id_estacion')
            ->join('sedes as sede', 'sede.idsedes', '=', 'estacion.idsede')
            ->where(DB::raw('DATE(fichapacientes.created_at)'), $fechaActual)
            // ->where('estacion.idestaciones', 11)
            ->where('sede.idsedes', 3)
            ->orderByRaw('LENGTH(estacion.nombre_estacion)', 'ASC')
            ->orderby('estacion.nombre_estacion', 'ASC')
            ->get();
        //dd($pacientes);
        /*->with(["Fichapaciente.Estacion" => function ($q) {
                $q->where('estaciones.idsede', '=', 1);
                $q->orWhere('estaciones.idsede', '=', 2);
                $q->orWhere('estaciones.idsede', '=', 3);
                $q->orWhere('estaciones.idsede', '=', 5);
            }])*/
        foreach ($pacientes as $paciente) {
            $paciente->end = '';
        }


        $primero =  FichaPaciente::select('fichapacientes.created_at as start')
            ->join('estaciones as estacion', 'estacion.idestaciones', '=', 'fichapacientes.id_estacion')
            ->join('sedes as sede', 'sede.idsedes', '=', 'estacion.idsede')
            ->where(DB::raw('DATE(fichapacientes.created_at)'), $fechaActual)
            // ->where('estacion.idestaciones', 11)
            ->where('sede.idsedes', 3)


            ->oldest('fichapacientes.created_at')->first();
        $ultimo = FichaPaciente::select('fichapacientes.created_at as start')
            ->join('estaciones as estacion', 'estacion.idestaciones', '=', 'fichapacientes.id_estacion')
            ->join('sedes as sede', 'sede.idsedes', '=', 'estacion.idsede')
            ->where(DB::raw('DATE(fichapacientes.created_at)'), $fechaActual)
            // ->where('estacion.idestaciones', 11)
            ->where('sede.idsedes', 3)


            ->latest('fichapacientes.created_at')->first();
        // return $pacientes;
        return [


            'data' =>  $pacientes,
            'primero' => $primero,
            'ultimo' => $ultimo,

        ];
    }
    public function tiemposChilina(Request $request)
    {
        // if (!$request->ajax()) return redirect('/');
        $inicio = $request->fecha1;
        $fin = $request->fecha2;
        //$inicio = '2020-11-17';
        //$fin = '2020-11-17';
        $fechaActual = date('Y-m-d');
        // $fechaActual = '2020-11-26';

        $pacientes  = FichaPaciente::select(
            'fichapacientes.created_at as start',
            DB::raw("CONCAT(sede.abreviacion,' ',estacion.nombre_estacion) AS task")
        )
            ->join('estaciones as estacion', 'estacion.idestaciones', '=', 'fichapacientes.id_estacion')
            ->join('sedes as sede', 'sede.idsedes', '=', 'estacion.idsede')
            ->where(DB::raw('DATE(fichapacientes.created_at)'), $fechaActual)
            // ->where('estacion.idestaciones', 11)
            ->where('sede.idsedes', 1)
            ->orderByRaw('LENGTH(estacion.nombre_estacion)', 'ASC')
            ->orderby('estacion.nombre_estacion', 'ASC')
            ->get();
        //dd($pacientes);
        /*->with(["Fichapaciente.Estacion" => function ($q) {
                $q->where('estaciones.idsede', '=', 1);
                $q->orWhere('estaciones.idsede', '=', 2);
                $q->orWhere('estaciones.idsede', '=', 3);
                $q->orWhere('estaciones.idsede', '=', 5);
            }])*/
        foreach ($pacientes as $paciente) {
            $paciente->end = '';
        }


        $primero =  FichaPaciente::select('fichapacientes.created_at as start')
            ->join('estaciones as estacion', 'estacion.idestaciones', '=', 'fichapacientes.id_estacion')
            ->join('sedes as sede', 'sede.idsedes', '=', 'estacion.idsede')
            ->where(DB::raw('DATE(fichapacientes.created_at)'), $fechaActual)
            // ->where('estacion.idestaciones', 11)
            ->where('sede.idsedes', 3)


            ->oldest('fichapacientes.created_at')->first();
        $ultimo = FichaPaciente::select('fichapacientes.created_at as start')
            ->join('estaciones as estacion', 'estacion.idestaciones', '=', 'fichapacientes.id_estacion')
            ->join('sedes as sede', 'sede.idsedes', '=', 'estacion.idsede')
            ->where(DB::raw('DATE(fichapacientes.created_at)'), $fechaActual)
            // ->where('estacion.idestaciones', 11)
            ->where('sede.idsedes', 3)


            ->latest('fichapacientes.created_at')->first();
        // return $pacientes;
        return [


            'data' =>  $pacientes,
            'primero' => $primero,
            'ultimo' => $ultimo,

        ];
    }
    public function totalBarrasComplejoInicial(Request $request)
    {

        $fecha = $request->fecha;

        // if (!$request->ajax()) return redirect('/');

        $fechaActual = $fecha;
        //$fechaActual2 = '2020-08-20';
        //echo $fechaActual;


        $Serologicas  = FichaPaciente::select(

            'estacion.nombre_estacion as year',
            DB::raw('count(*) as europe')
        )
            ->leftJoin('estaciones as estacion', 'estacion.idestaciones', '=', 'fichapacientes.id_estacion')
            ->leftJoin('sedes as sede', 'sede.idsedes', '=', 'estacion.idsede')
            ->groupBy('estacion.idestaciones')
            ->whereDate('fichapacientes.created_at', "=", $fechaActual)
            ->whereHas('PruebaSerologica')
            ->where('sede.idsedes', 3)
            ->get();
        $Serologicastotal  = FichaPaciente::select(

            'estacion.nombre_estacion as year'
        )
            ->leftJoin('estaciones as estacion', 'estacion.idestaciones', '=', 'fichapacientes.id_estacion')
            ->leftJoin('sedes as sede', 'sede.idsedes', '=', 'estacion.idsede')
            ->whereDate('fichapacientes.created_at', "=", $fechaActual)
            ->whereHas('PruebaSerologica')
            ->where('sede.idsedes', 3)
            ->get()
            ->count();
        /* 
        $total_asum = PcrPruebaMolecular::select(DB::raw('count(*) data3'), DB::raw("DATE_FORMAT(created_at, '%Y') year"), DB::raw('MONTH(created_at) months'),  DB::raw('MONTHNAME(created_at) month'))
        ->where('tipo', '3')
        ->groupby('month')
        ->orderBy('year', 'ASC')
        ->orderBy('months', 'ASC')
        ->get();*/

        $Moleculares = FichaPaciente::select(

            'estacion.nombre_estacion as year',
            DB::raw('count(*) as namerica')
        )
            ->leftJoin('estaciones as estacion', 'estacion.idestaciones', '=', 'fichapacientes.id_estacion')
            ->leftJoin('sedes as sede', 'sede.idsedes', '=', 'estacion.idsede')
            ->groupBy('estacion.idestaciones')
            ->whereDate('fichapacientes.created_at', "=", $fechaActual)
            ->whereHas('PcrPruebaMolecular')
            ->where('sede.idsedes', 3)
            ->get();

        $Molecularestotal = FichaPaciente::select(
            'estacion.nombre_estacion as year'
        )
            ->leftJoin('estaciones as estacion', 'estacion.idestaciones', '=', 'fichapacientes.id_estacion')
            ->leftJoin('sedes as sede', 'sede.idsedes', '=', 'estacion.idsede')
            ->whereDate('fichapacientes.created_at', "=", $fechaActual)
            ->whereHas('PcrPruebaMolecular')
            ->where('sede.idsedes', 3)
            ->get()
            ->count();

        return [
            'serologicas' => $Serologicas,
            'moleculares' => $Moleculares,
            'serologicastotal' => $Serologicastotal,
            'molecularestotal' => $Molecularestotal,
        ];
    }
    public function totalBarrasComplejo(Request $request)
    {



        // if (!$request->ajax()) return redirect('/');
        $fecha = $request->fecha;
        $fechaActual = $fecha;
        //$fechaActual2 = '2020-08-20';
        //echo $fechaActual;


        $Serologicas  = FichaPaciente::select(

            'estacion.nombre_estacion as year',
            DB::raw('count(*) as europe')
        )
            ->leftJoin('estaciones as estacion', 'estacion.idestaciones', '=', 'fichapacientes.id_estacion')
            ->leftJoin('sedes as sede', 'sede.idsedes', '=', 'estacion.idsede')
            ->groupBy('estacion.idestaciones')
            ->whereDate('fichapacientes.created_at', "=", $fechaActual)
            ->whereHas('PruebaSerologica')
            ->where('sede.idsedes', 3)
            ->get();


        $Serologicastotal  = FichaPaciente::select(

            'estacion.nombre_estacion as year'
        )
            ->leftJoin('estaciones as estacion', 'estacion.idestaciones', '=', 'fichapacientes.id_estacion')
            ->leftJoin('sedes as sede', 'sede.idsedes', '=', 'estacion.idsede')
            ->whereDate('fichapacientes.created_at', "=", $fechaActual)
            ->whereHas('PruebaSerologica')
            ->where('sede.idsedes', 3)
            ->get()
            ->count();

        /* 
        $total_asum = PcrPruebaMolecular::select(DB::raw('count(*) data3'), DB::raw("DATE_FORMAT(created_at, '%Y') year"), DB::raw('MONTH(created_at) months'),  DB::raw('MONTHNAME(created_at) month'))
        ->where('tipo', '3')
        ->groupby('month')
        ->orderBy('year', 'ASC')
        ->orderBy('months', 'ASC')
        ->get();*/

        $Moleculares = FichaPaciente::select(

            'estacion.nombre_estacion as year',
            DB::raw('count(*) as namerica')
        )
            ->leftJoin('estaciones as estacion', 'estacion.idestaciones', '=', 'fichapacientes.id_estacion')
            ->leftJoin('sedes as sede', 'sede.idsedes', '=', 'estacion.idsede')

            ->groupBy('estacion.idestaciones')
            ->whereDate('fichapacientes.created_at', "=", $fechaActual)
            ->whereHas('PcrPruebaMolecular')
            ->where('sede.idsedes', 3)
            ->get();

        $Molecularestotal = FichaPaciente::select(
            'estacion.nombre_estacion as year'
        )
            ->leftJoin('estaciones as estacion', 'estacion.idestaciones', '=', 'fichapacientes.id_estacion')
            ->leftJoin('sedes as sede', 'sede.idsedes', '=', 'estacion.idsede')
            ->whereDate('fichapacientes.created_at', "=", $fechaActual)
            ->whereHas('PcrPruebaMolecular')
            ->where('sede.idsedes', 3)
            ->get()
            ->count();

        return [
            'serologicas' => $Serologicas,
            'moleculares' => $Moleculares,
            'serologicastotal' => $Serologicastotal,
            'molecularestotal' => $Molecularestotal,
        ];
    }
}
