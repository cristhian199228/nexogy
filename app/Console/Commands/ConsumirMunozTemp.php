<?php

namespace App\Console\Commands;

use App\FichaPaciente;
use App\MensajeWhatsApp;
use App\PcrPruebaMolecular;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use SoapClient;

class ConsumirMunozTemp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consumir:ws_munoz_pcr_temp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $fechaActual = date('Y-m-d');
        $fechaAnterior = date('Y-m-d',strtotime("-1 days"));
        $hora = date('H');

        if ($hora >= 17 || $hora <= 6) {

            $fichas = FichaPaciente::whereBetween(DB::raw('DATE(created_at)'),[$fechaAnterior,$fechaActual])
                ->whereHas('PacienteIsos')
                ->whereHas('PcrPruebaMolecular', function ($q) {
                    $q//->whereHas("PcrEnvioMunoz")
                    ->whereNull('resultado');
                })
                ->with("PacienteIsos")
                ->with('PcrPruebaMolecular')
                ->get();

            foreach ($fichas as $ficha){
                $document = $ficha->PacienteIsos->numero_documento;
                $estado = 0;

                $params = array(
                    "document" => $document,
                    "day" => Carbon::parse($ficha->created_at)->format('d'),
                    "month" => Carbon::parse($ficha->created_at)->format('m'),
                    "year" => Carbon::parse($ficha->created_at)->format('Y'),
                );

                $client = new SoapClient( 'http://207.246.113.125/lmsermedi/webservices/ConnectorAppLab.svc?wsdl' );
                $respuesta_ws = $client->GetResultsByDocumentAndDate( $params);

                if(!is_null($respuesta_ws->GetResultsByDocumentAndDateResult)) {

                    if(!is_array($respuesta_ws->GetResultsByDocumentAndDateResult->Results->Results) &&
                        $respuesta_ws->GetResultsByDocumentAndDateResult->Results->Results->TestCode === "PML01") {
                        $estado = 1;
                        $resultado_pm_munoz = $respuesta_ws->GetResultsByDocumentAndDateResult->Results->Results->Result;

                    } else {
                        $examenes = $respuesta_ws->GetResultsByDocumentAndDateResult->Results->Results;

                        foreach ($examenes as $examen) {
                            if($examen->TestCode === "PML01") {
                                $estado = 2;
                                $resultado_pm_munoz = $examen->Result;
                            } else {
                                $estado = 3;
                                $resultado_pm_munoz = "";
                            }
                        }

                    }
                    if($resultado_pm_munoz !== "") {
                        $resultado_pm_munoz = trim($resultado_pm_munoz);

                        if($resultado_pm_munoz === "POSITIVO") {
                            $resultado_pm = 1;

                        } elseif ($resultado_pm_munoz === "NEGATIVO") {
                            $resultado_pm = 0;

                        } elseif ($resultado_pm_munoz === "NEG") {
                            $resultado_pm = 0;

                        } elseif ($resultado_pm_munoz === "POS") {
                            $resultado_pm = 1;

                        } else {
                            $resultado_pm = null;
                        }

                        $pcr = PcrPruebaMolecular::find($ficha->PcrPruebaMolecular->idpcr_pruebas_moleculares);
                        $pcr->resultado = $resultado_pm;
                        $pcr->consumed_at = date('Y-m-d H:i:s');
                        $pcr->save();

                        $celular = "51" . $ficha->PacienteIsos->celular;
                        $fecha = $ficha->created_at->format('d-m-Y');
                        $wp = new MensajeWhatsApp($celular, true);
                        $paciente = $ficha->PacienteIsos;
                        $nombre_paciente = $paciente->nombres. " " .$paciente->apellido_paterno. " " . $paciente->apellido_materno;
                        $empresa = $paciente->Empresa->descripcion;

                        if($ficha->Estacion->Sede->idsedes !== 5 && $pcr->resultado === 1){
                            $text_grupo =  "Se encontró un resultado *".getStrPcrResult($pcr->resultado)."* de prueba molecular del día ".
                                $fecha . ". Paciente: *". $nombre_paciente ."*, número de documento: " . $paciente->numero_documento .
                                ", Empresa: ". $empresa .", numero de celular: ". $paciente->celular;
                            $wp->setChatId("51961498695-1618362899@g.us");
                            $wp->sendMessageByChatId($text_grupo);
                        }
                    }
                }
            }
        }

        return 0;
    }
}
