<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha Ep.</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            font-family: "Gill Sans Extrabold", Helvetica, sans-serif
        }
        .container {
            margin: 2rem 3rem;
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
        table {
            border-collapse: collapse;
            margin: 0 auto;
            width: 100%;
            font-size: 12px;
        }
        td, th {
            text-align: center;
            border: 1px solid #999;
            padding: 0.1rem;
        }
        .firma {
            margin: auto;
            text-align: center;
            margin-top: 2em;
        }
        header {
            margin-bottom: 1.5em;
            text-align: center;
        }
        h4 {
            margin-top: 1.5em;
            text-decoration: underline;
        }
        .texto {
            padding: 0.8em;
            line-height: 1.5;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="logo">
        <img align="right" src="img/logo_isos.png" width="130px" alt="logo_isos">
    </div>
    <header>
        <h4>FICHA DE INFORMACION CON DATOS EPIDEMIOLOGICOS</h4>
    </header>
    <main>
        <div class="texto">
            <p>
                <b>Nombres y apellidos: </b>{{ $ficha['nom_completo']  }}
            </p>
            <p>
                <b>Dirección: </b>{{ $ficha['direccion']  }}
            </p>
            <div>
                <p class="alignleft"><b>DNI: </b>{{ $ficha['dni'] }}</p>
                <p class="alignright"><b>Celular: </b>{{ $ficha['celular'] }}</p>
            </div>
            <div style="clear: both;">
                <p class="alignleft"><b>Empresa: </b>{{ $ficha['empresa'] }}</p>
                <p class="alignright"><b>Registro: </b>{{ $ficha['registro'] }}</p>
            </div>
            <div style="clear: both;">
                <p class="alignleft"><b>Nombre supervisor: </b>{{ $ficha['supervisor'] }}</p>
                <p class="alignright"><b>Celular: </b>{{ $ficha['cel_supervisor'] }}</p>
            </div>
            <div style="clear: both;">
                <p>
                    <b>Periodo de estancia en mina:</b>
                    @if($ficha['p_inicio'] && $ficha['p_final'])
                        {{  $ficha['p_inicio'].' al '.$ficha['p_final']}}
                    @endif
                </p>
            </div>
            <div style="clear: both;">
                <p><b>¿Salió POSITIVO en una prueba Serológica (Rápida) o Molecular (Hisopado) previa a la fecha?: </b>
                    @if(!is_null($ficha['prueba_positiva']))
                        {{ $ficha['prueba_positiva'] ? 'SI' : 'NO' }}
                    @endif
                </p>
                <p><b>¿La prueba POSITIVA fue en el Tamizaje de Cerro verde?: </b>
                    @if(!is_null($ficha['prueba_cv']))
                        {{ $ficha['prueba_cv'] ? 'SI' : 'NO' }}
                    @endif
                </p>
                <p><b>¿La prueba POSITIVA fue en alguna otra institución o laboratorio?: </b>
                    @if(!is_null($ficha['prueba_otro']))
                        {{ $ficha['prueba_otro'] ? 'SI' : 'NO' }}
                    @endif
                </p>
                <br>
                @if(!is_null($ficha['prueba_otro']) && $ficha['prueba_otro'] === 1 )
                    <div class="tabla">
                        <table>
                            <thead>
                            <tr>
                                <td colspan="4">
                                    <b>PRUEBA REALIZADA EN OTRO LABORATORIO</b>
                                </td>
                            </tr>
                            <tr>
                                <th>Fecha</th>
                                <th>Lugar</th>
                                <th>Tipo</th>
                                <th>Resultado</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{{ $ficha['prueba_otro_fecha'] }}</td>
                                <td>{{ $ficha['prueba_otro_lugar'] }}</td>
                                <td>
                                    @if(!is_null($ficha['prueba_otro_tipo']))
                                        @switch($ficha['prueba_otro_tipo'])
                                            @case(1)
                                            PRUEBA MOLECULAR
                                            @break
                                            @case(2)
                                            PRUEBA SEROLOGICA
                                            @break
                                            @case(3)
                                            PRUEBA ANTIGENA
                                            @break
                                        @endswitch
                                    @endif
                                </td>
                                <td>
                                    @if(!is_null($ficha['prueba_otro_resultado']))
                                        @switch($ficha['prueba_otro_resultado'])
                                            @case(0)
                                            NEGATIVO
                                            @break
                                            @case(1)
                                            POSITIVO
                                            @break
                                            @case(2)
                                            IGG
                                            @break
                                            @case(3)
                                            IGM
                                            @break
                                            @case(4)
                                            IGM/IGG
                                            @break
                                        @endswitch
                                    @endif
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <br>
                @endif
        </div>
            @if(count($ficha['contactos']) > 0)
                <div class="tabla">
                    <table>
                        <thead>
                        <tr>
                            <td colspan="4">
                                <b>LISTA DE CONTACTOS DIRECTOS</b>
                            </td>
                        </tr>
                        <tr>
                            <th>Nombres</th>
                            <th>Celular</th>
                            <th>Cargo</th>
                            <th>Detalle</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach( $ficha['contactos'] as $cd )
                            <tr>
                                <td>{{ $cd['nombres'] }}</td>
                                <td>{{ $cd['celular'] }}</td>
                                <td>{{ $cd['cargo'] }}</td>
                                <td>{{ $cd['detalle'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <br>
            @else
                <p>
                    <b>Contactos directos: </b>NO REGISTRA
                </p>
            @endif
            <p>
                <b>Observaciones: </b>{{ $ficha['observaciones'] }}
            </p>
            <br>
            <p>
                Arequipa, {{ $ficha['fecha'] }}
            </p>
            <br>
            <div class="firma">
                @if($ficha['firma'])
                    <img width="250px" src="{{ $ficha['firma']  }}" />
                @endif
                <hr style="width:40%; margin: auto;">
                FIRMA
            </div>
        </div>
    </main>
</div>
</body>
</html>
