<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Certificado COVID-19</title>
    <style>
        body {
            text-align: center;
            text-transform: uppercase;
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
        }

        h3 {
            text-align: center;
            text-transform: uppercase;
            font-size: 18px;
        }

        .contenido {
            font-size: 15px;
            margin-left: 100px;
            font-family: Arial, Helvetica, sans-serif;
            margin-right: 50px;
        }

        .imagen {
            position: absolute;
            top: 10px;
            left: 20px;
        }

        .imagen_firma {
            position: absolute;
            top: 725px;
            left: 280px;
        }

        #primero {
            background-color: #ccc;
        }

        #segundo {
            text-align: left;
            color: #000000;
        }

        #segundo_prueba {
            text-align: center;
            color: #000000;
            margin: auto;
            border: 3px solid #000000;
        }

        #segundo_resultado {
            text-align: center;
            color: #49CB04;
        }

        #tercero {
            left: 20px;
        }

        #cuarto {
            padding-left: -20px;
        }

        #firma {
            padding-left: -20px;
            font-size: 10px;
            line-height: 12px;
            font-weight: bolder;
        }

        div.centered {
            text-align: center;
        }

        table {
            border-collapse: collapse;
        }

        div.centered table,

        td {
            margin: auto;
            border: 2px solid black;
            text-align: center;
            height: 40px;
            width: 80px;
        }
    </style>
</head>

<body>
    <img class="imagen" src="img/logo_prueba_rapidez.png" alt="Logo" height="50px">
    <br>
    <br>
    <br>
    <br>
    <br>
    <h3><strong>CONSTANCIA DE RESULTADOS <br>PRUEBA SEROLÓGICA COVID-19</strong></h3>
    <div class="contenido">
        <p id="segundo"><strong>Nombre : </strong>{{ $paciente->nombre_completo }}</p>
        <p id="segundo"><strong>Edad :</strong>{{$paciente->edad}} años</p>
        <p id="segundo"><strong>Documento de Identidad : </strong>{{$paciente->dni}}</p>
        @if($paciente->empresa == null)
        <p id="segundo"><strong>Empresa : </strong>SOCIEDAD MINERA CERRO VERDE</p>
        @else
        <p id="segundo"><strong>Empresa : </strong>{{$paciente->empresa->descripcion}}</p>
        @endif
        <p id="segundo"><strong>Fecha : </strong>{{$paciente->fecha}}</p>
        <br>
        <br>
        <p id="segundo"><strong>ANTECEDENTES CLÍNICOS Y/O EPIDEMIOLÓGICOS :</strong></p>
        <br>
        @if($paciente->datosclinicos != null)
        @foreach($paciente->datosclinicos as $datosclinicosf)
        @php
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
        @endphp
        <p id="segundo">{{$result}}</p>
        @endforeach

        @endif


        @if($paciente->antecedentesep != null)
        @foreach($paciente->antecedentesep as $antecedentesepf)
        @php
        $antec = array();
        if ($antecedentesepf->dias_viaje == 1)
        array_push($antec, 'ha viajado en los ultimos 14 dias');
        if ($antecedentesepf->contacto_cercano == 1)
        array_push($antec, 'contacto cercano');
        if ($antecedentesepf->conv_covid == 1)
        array_push($antec, 'conversacion con sospechoso covid 19');
        if (is_null($antecedentesepf->debilite_sistema)) {
        } else {
        array_push($antec, 'Condicion que debilite sistema:' . $antecedentesepf->debilite_sistema);
        }
        if (!empty($antecedentesepf->paises_visitados)) {
        array_push($antec, 'otros:(' . $antecedentesepf->paises_visitados . ')');
        }
        $result2 = implode(", ", $antec);
        @endphp
        <p id="segundo">{{$result2}}
            @endforeach

            @endif
            </strong</p> <p id="segundo_prueba"><strong>PRUEBA SEROLÓGICA (RÁPIDA) SARS COV 2 (COVID-19)</strong</p> <br>
                <br>
                <br>


                @php


                $resultado = 'NEGATIVO'
                @endphp


                <p id="segundo"><strong>RESULTADO</strong</p> <p id="segundo_resultado"><strong>{{$resultado}}</strong></p>

                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>

                <br>
                <br>
                <p id="tercero">
                    <hr width="250px;">
                </p>
                <img class="imagen_firma" src="img/firmaMarcoBautista.png" alt="Logo" height="80px">
                <div id="firma">SERVICIOS MEDICOS INTEGRADOS SAC</div>
                <div id="firma">MARCO ANTONIO BAUTISTA MACEDO</div>
                <div id="firma">MEDICO OCUPACIONAL</div>

                <div id="firma">CMP 43051</div>

    </div>
</body>

</html>
