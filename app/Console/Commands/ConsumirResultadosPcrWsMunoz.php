<?php

namespace App\Console\Commands;

use App\EnvioWpPcr;
use App\FichaPaciente;
use App\MensajeWhatsApp;
use App\PcrConsumoMunoz;
use App\PcrPruebaMolecular;
use App\Service\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use SoapClient;

class ConsumirResultadosPcrWsMunoz extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consumir:ws_munoz_pcr';

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
        $horaActual = date('H:i:s');
        $fechaActual = date('Y-m-d');
        $fechaAnterior = date('Y-m-d',strtotime("-4 days"));
        //$fechaActual = '2020-11-07';
        $hora = date('H');

        if ($hora >= 17 || $hora <= 6) {

            $fichas = FichaPaciente::whereBetween(DB::raw('DATE(created_at)'),[$fechaAnterior,$fechaActual])
                ->whereHas('PacienteIsos')
                ->whereHas('PcrPruebaMolecular', function ($q) {
                    $q->whereHas("PcrEnvioMunoz")
                        ->whereNull('resultado');
                })
                ->with("PacienteIsos")
                ->with("Estacion.Sede")
                ->with('PcrPruebaMolecular.PcrEnvioMunoz')
                ->get();

            foreach ($fichas as $ficha) {

                $id_pcr_envio_munoz = $ficha->PcrPruebaMolecular->PcrEnvioMunoz->idpcr_envio_munoz;
                //$respuesta_ws = "";
                $document = $ficha->PacienteIsos->numero_documento;
                $estado = 0;
                $diasABuscar = $ficha->Estacion->Sede->idsedes == 4 ? 3 : 1;

                for ($i = 0; $i <= $diasABuscar; $i++) {
                    $fecha = $ficha->created_at->addDays($i);
                    $params = array(
                        "document" => $document,
                        "day" => $fecha->format('d'),
                        "month" => $fecha->format('m'),
                        "year" => $fecha->format('Y'),
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

                            $fecha = $ficha->created_at->format('d-m-Y');
                            $paciente = $ficha->PacienteIsos;
                            $wp = new WhatsAppService($paciente->celular);
                            $empresa = $paciente->Empresa->descripcion;

                            if($ficha->Estacion->Sede->idsedes !== 5 && $pcr->resultado === 1){
                                $text_grupo =  "Se encontró un resultado *".getStrPcrResult($pcr->resultado)."* de prueba molecular del día ".
                                    $fecha . ". Paciente: *". $paciente->full_name ."*, número de documento: " . $paciente->numero_documento .
                                    ", Empresa: ". $empresa .", numero de celular: ". $paciente->celular;
                                $wp->setChatId("51961498695-1618362899@g.us");
                                $wp->sendMessageByChatId($text_grupo);
                            }
                        }
                    }

                    $consumo = new PcrConsumoMunoz();
                    $consumo->idpcr_envio_munoz = $id_pcr_envio_munoz;
                    $consumo->fecha = $fechaActual;
                    $consumo->hora  = $horaActual;
                    $consumo->respuesta_ws = json_encode($respuesta_ws);
                    $consumo->estado = $estado;
                    $consumo->save();
                }
            }

        }
        return 0;

    }
}
