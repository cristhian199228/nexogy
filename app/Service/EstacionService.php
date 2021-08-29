<?php

namespace App\Service;

use App\Estacion;
use Illuminate\Support\Facades\DB;

class EstacionService {

    public function getEstaciones() {

        /*$estaciones = Estacion::select('idestaciones',DB::raw('nombre_estacion as int_estacion'), 'idsede')
            ->with("Sede")->where("idsede", '<>', 3)
            ->get();

        $estaciones_complejo = Estacion::select('idestaciones', DB::raw('convert(nombre_estacion, unsigned integer) as int_estacion'), 'idsede')
            ->where("idsede", 3)
            ->get();*/
        $sedes = ['','CHILINA','JUAN PABLO','COMPLEJO'];
        $estaciones = Estacion::all();

        $estacionesComplejo = $estaciones->filter(function ($estacion, $key) {
            return $estacion->idsede == 1 || $estacion->idsede == 2 || $estacion->idsede === 3;
        })->each(function ($estacion) use ($sedes) {
            $estacion->nom_estacion = $sedes[$estacion->idsede] . $estacion->nombre_estacion;
        })->sortBy('idsede', SORT_REGULAR)->values();


        return $estaciones;

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