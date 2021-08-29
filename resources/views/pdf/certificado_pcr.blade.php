<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace;
        }
        .container {
            margin-left: 1.5em;
            margin-right: 1.5em;
            margin-top: 2.5em;
        }
        header {
            margin-bottom: 2em;
            text-align: center;
        }
        h1 {
            margin-bottom: 0.5em;
            margin-top: 1.5em;
        }
        h1, h2 {
            color: #00008B;
        }
        .caja {
            border: 1px solid #00008B;
            border-radius: 0.7em;
            margin-bottom: 1em;
            padding: 0.8em;
            font-size: 0.7em;
            line-height: 1.5;
        }

        span{
            font-weight: bold;
        }
        table {
            font-size: 0.7em;
            line-height: 1.5;
            border-collapse: collapse;
        }
        td, th {
            text-align: center;
            border: 1px solid #999;
            padding: 0.3rem;
        }
        .alignleft {
            float: left;
        }
        .alignright {
            float: right;
            margin-right: 1em;
        }
        .firma {
            text-align: center;
            margin-top: 4em;
        }
        #codigo_qr {
            position: fixed !important;
            bottom: 9.5%;
            left: 1.5em;
        }
        #watermark {
            position: fixed;

            /**
                Set a position in the page for your image
                This should center it vertically
            **/
            bottom:   11cm;
            left:     2.5cm;

            /** Change image dimensions**/
            width:    8cm;
            height:   8cm;

            /** Your watermark should be behind every content**/
            z-index:  -1000;
            opacity: 0.15;
        }

    </style>
    <title>Certificado PCR {{ $constancia['id'] }}</title>
</head>
<body>
<div class="container">
    <div class="logo">
        <img align="left" src="img/logo_isos.png" width="110px" alt="logo_isos">
        <img align="right" src="img/logo_munoz3.png" width="130px"  alt="logo_munos">
    </div>
    <header>
        <h1>LABORATORIO PRIVADO</h1>
        <h2>SERVICIOS MÉDICOS INTEGRADOS S.A.C.</h2>
        <h2>LABORATORIOS MUÑOZ E.I.R.L.</h2>
        <br>
        <h3>INFORME DE RESULTADO: {{ $constancia['id'] }}</h3>
    </header>
    <div id="watermark">
        <img src="img/logo_prueba_rapidez.png" height="100%"/>
    </div>
    <main>
        <div class="caja">
            <span>IDENTIFICACIÓN DEL PACIENTE: </span>{{ $constancia['nom_completo'] }}<br>
            <div>
                <p class="alignleft"><span>TIPO DE DOCUMENTO: </span>{{ $constancia['tipo_documento'] }}</p>
                <p class="alignright"><span>N° DOCUMENTO: </span>{{ $constancia['nro_documento'] }}&nbsp;</p>
            </div>
            <div style="clear: both">
                <p class="alignleft"><span>EDAD: </span>{{ $constancia['edad'] }}</p>
                <p class="alignright"><span>SEXO: </span>{{ $constancia['sexo']  }}&nbsp;</p>
            </div>
            <div style="clear: both">
                <p class="alignleft"><span>CÓDIGO DE ORDEN: </span>{{ $constancia['codigo_orden']  }}</p>
                <p class="alignright"><span>TELÉFONO: </span>{{ $constancia['telefono']  }}&nbsp;</p>
            </div>
            <div style="clear: both">
                <p class="alignleft"><span>DIRECCIÓN/ UBICACIÓN: </span>{{ $constancia['direccion']  }}</p><br>
                <br>
            </div>
        </div>
        <div class="caja">
            <p>
                <span>SOLICITANTE: </span>DR. Marco Antonio Bautista Macedo &nbsp;
                <span>CMP: </span>43051
                <span>IPRESS: </span>00028020-POLICLÍNICO INTERNATIONAL SOS
            </p>
            <span>UBICACIÓN: </span>Av. Parra No. 324 / 326. Distrito Arequipa, Provincia Arequipa, Departamento Arequipa<br>
            <span>DOCUMENTO DE REFERENCIA:  </span><br>
        </div>
        <div class="caja">
            <span>FECHA INGRESO MUESTRA EN RECEPCIÓN: </span>{{ $constancia['fecha_muestra']  }}<br>
            <span>LUGAR DE LA TOMA DE MUESTRA: </span>00028020 – POLICLÍNICO INTERNATIONAL SOS<br>
            <span>UBICACIÓN: </span>Av. Parra No. 324 / 326. Distrito Arequipa, Provincia Arequipa, Departamento Arequipa   <br>
        </div>
        <br>
        <div class="tabla">
            <table>
                <thead>
                <tr>
                    <td colspan="5">
                        <span>TIPO DE MUESTRA PRIMARIA: </span>HISOPADO NASAL Y FARÍNGEO &nbsp;
                        <span>FECHA DE OBTENCIÓN: </span>{{ $constancia['fecha_muestra'] }} – {{ $constancia['hora_muestra'] }}<br>
                        <span>CÓDIGO DE MUESTRA: </span>{{ $constancia['codigo_muestra'] }}
                    </td>
                </tr>
                <tr>
                    <th>FECHA DE RESULTADO</th>
                    <th>ANÁLISIS</th>
                    <th>COMPONENTE</th>
                    <th>MÉTODO</th>
                    <th>RESULTADO</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>{{ $constancia['fecha_resultado'] }}</td>
                    <td>SARS - coronavirus 2 ARN (presencial) en muestra respiratoria por PT-PCR en tiempo real</td>
                    <td>SARS-CORONAVIRUS-2-ARN</td>
                    <td>RT-PCR en tiempo real</td>
                    <td>{{ $constancia['res'] }}</td>
                </tr>
                </tbody>
            </table>
        </div>
        <p class="firma">
            <img src="img/firma_dr_munoz.jpg" width="150px"  alt="firma">
        </p>
        <div id="codigo_qr">
            <img width="85px" src="data:image/svg;base64, {!! base64_encode(QrCode::format('svg')
                    ->size(200)
                    ->generate($constancia['url'])) !!} ">
        </div>
    </main>
</div>
</body>
</html>
