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
    <br>
    <div class="logo">
        <img align="right" src="img/logo_isos.png" width="130px" alt="logo_isos">
    </div>
    <header>
        <h4>INDICACIONES MÉDICAS</h4>
    </header>
    <main>
        <div class="texto">
            <p>
                {{ $data['descr_espvalorada'] }}
            </p>
        <br>
        <div class="firma">
            @if($data['firma_doctor'])
                <div class="alignleft">
                    <img width="250px" src="{{ $data['firma_doctor']  }}" />
                    <hr>
                    Dr. {{ $data['nombre_doctor']  }}
                </div>
            @endif
            @if($data['firma_paciente'])
                    <div class="alignright">
                        <img width="250px" src="{{ $data['firma_paciente']  }}" />
                        <hr>
                        Paciente: {{ $data['nombre_paciente'] }}
                    </div>
            @endif
        </div>
    </main>
</div>
</body>
</html>
