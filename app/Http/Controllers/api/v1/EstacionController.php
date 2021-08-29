<?php

namespace App\Http\Controllers\api\v1;

use App\Estacion;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class EstacionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response(['estaciones' => $this->estaciones()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        return response(array_filter($this->estaciones(), function ($e) use ($id) {
           return $e->idsede == $id;
        }));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function estaciones() {
        $estaciones = Estacion::select('idestaciones',DB::raw('nombre_estacion as int_estacion'), 'idsede')
            ->with("Sede")->where("idsede", '<>', 3)
            ->get();

        $estaciones_complejo = Estacion::select('idestaciones', DB::raw('convert(nombre_estacion, unsigned integer) as int_estacion'), 'idsede')
            ->where("idsede", 3)
            ->get();

        $arr_response = [];

        foreach ($estaciones_complejo as $estacion) {
            $estacion->nom_estacion = "COMPLEJO " . $estacion->int_estacion;
            array_push($arr_response, $estacion);
        }

        foreach ($estaciones as $estacion) {
            $id_sede = $estacion->Sede->idsedes;
            $nom_sede = "";
            switch ($id_sede) {
                case 1:
                    $nom_sede = "CHILINA "; break;
                case 2:
                    $nom_sede = "JUAN PABLO "; break;
            }
            $nom_estacion = $nom_sede. $estacion->int_estacion;
            switch ($estacion->idestaciones) {
                case 21:
                    $nom_estacion = "EXPATS"; break;
                case 22:
                    $nom_estacion = "C1 CONCENTRADORA"; break;
                case 23:
                    $nom_estacion = "POSTA SUR"; break;
                case 24:
                    $nom_estacion = "CLINICA ISOS SO"; break;
                case 25:
                    $nom_estacion = "CENTRO DE AISLAMIENTO"; break;
                case 26:
                    $nom_estacion = "PRUEBAS 1"; break;
                case 32:
                    $nom_estacion = "CLINICA ISOS SO PCR"; break;
                case 38:
                    $nom_estacion = "RESPONSE CENTER"; break;
            }
            $estacion->nom_estacion = $nom_estacion;
            unset($estacion->Sede);
            array_push($arr_response, $estacion);
        }

        return $arr_response;
    }
}
