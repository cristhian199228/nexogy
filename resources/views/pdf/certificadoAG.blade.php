<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Certificado PRS {{ $constancia['id'] }}</title>
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
            top: 815px;
            left: 280px;
        }

        #primero {
            background-color: #ccc;
        }

        .segundo {
            text-align: left;
            color: #000000;
        }

        .segundo_prueba {
            text-align: center;
            color: #000000;
            margin: auto;
            border: 3px solid #000000;
        }

        .negativo {
            text-align: center;
            color: #00cc00;
        }
        .positivo {
            text-align: center;
            color: red;
        }

        #tercero {
            left: 20px;
        }

        #cuarto {
            padding-left: -20px;
        }

        .firma {
            text-align: center;
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
        #codigo_qr {
            position: fixed !important;
            bottom: 7.5%;
            text-align: left;
        }
        #watermark {
            position: fixed;

            /**
                Set a position in the page for your image
                This should center it vertically
            **/
            bottom:   11cm;
            left:     5.5cm;

            /** Change image dimensions**/
            width:    8cm;
            height:   8cm;

            /** Your watermark should be behind every content**/
            z-index:  -1000;
            opacity: 0.15;
        }
    </style>
</head>

<body>
    <img class="imagen" src="img/logo_prueba_rapidez.png" alt="Logo" height="50px">
    <br>
    <br>
    <br>
    <br>
    <h3><strong>CONSTANCIA DE RESULTADOS <br>PRUEBA ANTÍGENA COVID-19 N° {{ $constancia['id'] }}</strong></h3>
    <div class="contenido">
        <p class="segundo"><strong>NOMBRE : </strong>{{ $constancia['nom_completo'] }}</p>
        <p class="segundo"><strong>EDAD :</strong>{{ $constancia['edad'] }} años</p>
        <p class="segundo"><strong>DOCUMENTO DE IDENTIDAD : </strong>{{ $constancia['nro_documento'] }}</p>
        <p class="segundo"><strong>EMPRESA : </strong>{{ $constancia['empresa']  }}</p>
        <p class="segundo"><strong>FECHA : </strong>{{ $constancia['fecha'] }}</p>
        <div id="watermark">
            <img src="img/logo_prueba_rapidez.png" height="100%"/>
        </div>
        <p class="segundo"><strong>ANTECEDENTES CLÍNICOS Y/O EPIDEMIOLÓGICOS :</strong></p>
        @if(!$constancia['dc'] && !$constancia['ae'])
            <p>NO PRESENTA</p>
        @endif
        @if($constancia['dc'])
        <p class="segundo">{{ $constancia['dc']  }}</p>
        @endif
        @if($constancia['ae'])
        <p class="segundo">{{ $constancia['ae']  }}</p>
        @endif
        <br>
        <p class="segundo_prueba"><strong>PRUEBA ANTÍGENA (RÁPIDA) SARS COV 2 (COVID-19)</strong></p>
        <br>
        <br>
        
            <p class="segundo"><strong>RESULTADO</strong></p>
            @if($constancia['res'] === 0)
                <p class="negativo"><strong>NEGATIVO</strong></p>
                @endif
                @if($constancia['res'] === 1)
                <p class="positivo"><strong>POSITIVO</strong></p>
                
            @endif
      
        <br>
        <br>
        <br>
        <img align="center"  src="img/firmaMarcoBautista.png" alt="Logo" width="200px">
        <hr width="250px" style="margin: 0px auto">
        <div class="firma">SERVICIOS MEDICOS INTEGRADOS SAC</div>
        <div class="firma">MARCO ANTONIO BAUTISTA MACEDO</div>
        <div class="firma">MEDICO OCUPACIONAL</div>
        <div class="firma">CMP 43051</div>
        <div id="codigo_qr">
            <img width="85px" src="data:image/svg;base64, {!! base64_encode(QrCode::format('svg')
                    ->size(200)
                    ->generate($constancia['url'])) !!} ">
        </div>
    </div>
</body>

</html>
