<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FORMATO CAM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            font-family: "Gill Sans Extrabold", Helvetica, sans-serif
        }
        .container {
            margin: 1rem 5rem;
        }
        main {
            font-size: 14px;
        }
        .alignleft {
            float: left;
        }
        .alignright {
            float: right;
            margin-right: 1em;
        }
        .firma {
            margin: auto;
            text-align: center;
            margin-top: 2em;
        }
        header {
            margin-bottom: 3rem;
            text-align: center;
        }
        h4 {
            margin-top: 4em;
            text-decoration: underline;
        }
        .texto {
            line-height: 1.5;
            text-align: justify;
        }
        .acepta {
            text-align: center;
            margin: 1.5em 0;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="logo">
        <img align="right" src="img/logo_cv.png" width="100px" alt="logo_isos">
    </div>
    <header>
        <h4>FORMATO CENTRO DE AISLAMIENTO</h4>
    </header>
    <main>
        <div class="texto">
            <p>
                YO <b>{{ $ficha['nom_completo'] }}</b> TRABAJADOR DE LA
                EMPRESA <b>{{ $ficha['empresa'] }}</b> CON REGISTRO N° <b>{{ $ficha['registro'] }}</b> Y CON DNI N°
                <b>{{ $ficha['dni'] }}</b> HE SIDO INFORMADO SOBRE EL CENTRO DE AISLAMIENTO, EL CUAL SE ENCUENTRA
                DISPONIBLE PARA TODOS LOS TRABAJADORES DE SMCV Y EL INGRESO AL MISMO ES DE FORMA VOLUNTARIA.
            </p>
            <br>
            <p>POR LO TANTO:</p>
        </div>
        <div class="acepta">
            @if(!is_null($ficha['acepta']))
                @if($ficha['acepta'] === 1)
                    <p>(<b>X</b>) ACEPTO &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ( ) NO ACEPTO</p>
                @else
                    <p>( ) ACEPTO &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; (<b>X</b>) NO ACEPTO</p>
                @endif
            @else
                <p>( ) ACEPTO &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ( ) NO ACEPTO</p>
            @endif
        </div>
        <p>HACER USO DEL CENTRO DE LAS INSTALACIONES DEL CENTRO DE AISLAMIENTO ANTES MENCIONADO.</p>
        <br>
        <br>
        <p>AREQUIPA, {{ $ficha['fecha'] }}</p>
        <br>
        <div class="firma">
            @if($ficha['firma'])
                <img width="250px" src="{{ $ficha['firma']  }}" />
            @endif
            <hr style="width:40%; margin: auto;">
            FIRMA
        </div>
    </main>
</div>
</body>
</html>
