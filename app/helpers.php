<?php

const COVID_URL = 'https://covid19.internationalsos-peru.com';
const CONSTANCIAS_URL = 'https://constancias.internationalsos-peru.com';
const EVIDENCIAS_URL = 'https://evidencias.internationalsos-peru.com';

function getPcrResultWhatsApp($pcr) {

    if($pcr->resultado) {
        $respuesta = [
            'resultado' => '🔴 POSITIVO',
            'comentario' => 'En breve lo contactarán para generar las indicaciones complementarias, dentro de las que se encuentra la necesidad de aislamiento.'
        ];
    } else{
        $respuesta = [
            'resultado' => '🟢 NEGATIVO',
            'comentario' => 'Su resultado implica que no presenta la enfermedad COVID-19, pero debe continuar con las medidas de bioseguridad de forma permanente.'
        ];
    }

    return $respuesta;
}

function getSintomasMw($sintomas) {

    $dc = [
        "p1" => "NO",
        "p2" => "NO",
        "p3" => "NO",
        "p4" => "NO",
        "p5" => "NO",
        "p6" => "NO",
        "p7" => "NO",
        "p8" => "NO",
        "p9" => "NO",
        "p10" => "NO",
        "p11" => "NO",
        "p12" => "NO",
        "p13" => "NO",
        "p14" => "NO",
        "p15" => "NO",
    ];

    if(count($sintomas) > 0) {
        $dc = [
            "p1" => $sintomas[0]["tos"],
            "p2" => $sintomas[0]["dolor_garganta"],
            "p3" => $sintomas[0]["congestion_nasal"],
            "p4" => $sintomas[0]["dificultad_respiratoria"],
            "p5" => $sintomas[0]["fiebre"],
            "p6" => $sintomas[0]["malestar_general"],
            "p7" => $sintomas[0]["diarrea"],
            "p8" => $sintomas[0]["nauseas_vomitos"],
            "p9" => $sintomas[0]["cefalea"],
            "p10" => $sintomas[0]["irritabilidad_confusion"],
            "p11" => $sintomas[0]["dolor_garganta"],
            "p12" => $sintomas[0]["falta_aliento"],
            "p13" => $sintomas[0]["anosmia_ausegia"],
            "p14" => $sintomas[0]["otros"],
            "p15" => $sintomas[0]["toma_medicamento"]
        ];
        foreach($dc as $key => $value) {
            if($value) {
                if ($value === 1) {
                    $dc[$key] = "SI";
                }
            } else {
                $dc[$key] = "NO";
            }
        }
    }

    return $dc;
}

function getAntecedentesMw($antecedentes) {

    $ant = [
        "ap1" => "NO",
        "ap2" => "NO",
        "ap3" => "NO",
        "ap4" => "NO",
    ];

    if(count($antecedentes) > 0) {
        $ant = [
            "ap1" => $antecedentes[0]["paises_visitados"],
            "ap2" => $antecedentes[0]["contacto_cercano"],
            "ap3" => $antecedentes[0]["conv_covid"],
            "ap4" => $antecedentes[0]["debilite_sistema"],
        ];
        foreach($ant as $key => $value) {
            if($value) {
                if ($value === 1) {
                    $ant[$key] = "SI";
                }
            } else {
                $ant[$key] = "NO";
            }
        }
    }

    return $ant;
}

function getTemperatura($temperatura) {
    $temp = 36.6;
    if(count($temperatura) > 0) {
        $temp = $temperatura[0]->valortemp;
    }
    return $temp;
}

function getStrTipoDocumento($tipo) {
    $str = "";
    if (!is_null($tipo)) {
        switch ($tipo) {
            case 1: $str = "DNI"; break;
            case 2: $str = "CE"; break;
            case 3: $str = "PASAPORTE"; break;
            case 7: $str = "RUT"; break;
        }
    }
    return $str;
}

function getStrTurno ($turno) {
    $res = "";
    if (!is_null($turno)) {
        switch ($turno) {
            case 1: $res = "SUBIDA - PRE EMBARQUE"; break;
            case 2: $res = "BAJADA - POST ROTACIÓN"; break;
            case 3: $res = "NO APLICA"; break;
        }
    }
    return $res;
}

function getStrRol ($rol) {
    $res = "";
    if (!is_null($rol)) {
        switch ($rol) {
            case 1: $res = "ACUARTELADO"; break;
            case 2: $res = "ITINERANTE"; break;
            case 3: $res = "NO APLICA"; break;
        }
    }
    return $res;
}

function getNomEstacion(object $estacion) {
    $res = "";
    if($estacion->Sede) $res = $estacion->Sede->abreviacion . $estacion->nombre_estacion;
    return $res;
}

function getStrPcrResult($res) {
    $str = "";
    if (!is_null($res)) {
        switch ($res) {
            case 0: $str = "NEGATIVO"; break;
            case 1: $str = "POSITIVO"; break;
            case 2: $str = "ANULADO"; break;
        }
    }
    return $str;
}

function eliminarEspacios($str) {
    $arr = explode(" ",$str);
    $arr_nom = [];
    foreach($arr as $sr) {
        if($sr) {
            array_push($arr_nom, $sr);
        }
    }
    return implode(" ", $arr_nom);
}

