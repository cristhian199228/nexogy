<?php

namespace App\Http\Controllers;

use App\ConsultaWhatsappAg;
use App\ConsultaWhatsAppPcr;
use App\ConsultaWhatsAppPrs;
use App\EnvioWpAg;
use App\MensajeWhatsApp;
use App\Service\PruebaAntigenaService;
use App\Service\PruebaMolecularService;
use App\Service\PruebaSerologicaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\EnvioWP;
use App\PruebaSerologica;
use App\EnvioWpPcr;
use App\PcrPruebaMolecular;

class BotController extends Controller
{
    //

    public function InicioBot(Request $request)
    {
        $json = file_get_contents('php://input');
        $decoded = json_decode($json, true);

        if (isset($decoded['messages'])) {
            //check every new message
            foreach ($decoded['messages'] as $message) {
                $text = explode(' ', trim($message['body']));
                $celular = explode('@', trim($message['author']));

                if (!$message['fromMe']) {
                    $wp = new MensajeWhatsApp($celular[0], true);
                    $wp->setChatId($message['chatId']);

                    $hora = date('H');
                    $fechaActual = date('Y-m-d');
                    $fechaAnterior = date('Y-m-d',strtotime("-1 days"));

                    $envio = EnvioWP::where('numero_celular', $celular[0])
                        ->where(DB::raw('DATE(created_at)'), $fechaActual)
                        ->withCount('consulta')->latest()->first();

                    if ($envio && $hora < 17) {
                        $prueba = PruebaSerologica::where('idpruebaserologicas', $envio->idpruebaserologicas)
                            ->whereHas('FichaPaciente')
                            ->with('FichaPaciente.PacienteIsos','FichaPaciente.DatosClinicos','FichaPaciente.AntecedentesEp')
                            ->whereNotNull('no_reactivo')
                            ->first();

                        if ($prueba && $prueba->Fichapaciente->PacienteIsos->numero_documento == $text[0]) {

                            if($envio->consulta_count === 0){
                                try {
                                    $service = new PruebaSerologicaService($prueba);
                                    $service->drawAndSaveImage();
                                    $resultado_prs = $service->whatsAppResult();
                                    $paciente = $prueba->Fichapaciente->PacienteIsos;
                                    $nombre_paciente = $paciente->nombres. " " .$paciente->apellido_paterno. " " . $paciente->apellido_materno;
                                    $res = $resultado_prs['resultado'];
                                    $clas = $resultado_prs['clasificacion'];
                                    $comentario = $resultado_prs['comentario'];

                                    if(count($prueba->Fichapaciente->DatosClinicos) > 0) {
                                        $res .= "/PRESENTA SINTOMATOLOGIA";
                                        $clas = "DEBE PERMANECER EN SALA DE ESPERA";
                                        $comentario = "En breve será contactado por el Área Médica para generar el proceso complementario. Debe permanecer en la Sala donde le han indicado que espere.";
                                    }
                                    if(count($prueba->Fichapaciente->AntecedentesEp) > 0) {
                                        $res .= "/PRESENTA ANTECEDENTES EPIDEMIOLÓGICOS";
                                        $clas = "DEBE PERMANECER EN SALA DE ESPERA";
                                        $comentario = "En breve será contactado por el Área Médica para generar el proceso complementario. Debe permanecer en la Sala donde le han indicado que espere.";
                                    }

                                    $mensaje_prs = '*Este es un mensaje automático*: Sr(a) ' . $nombre_paciente.', el resultado de su prueba serológica del dia ' .
                                        $prueba->created_at->format('d-m-Y') . ' es *' . $res . '*, por lo tanto  *'. $clas. "*. " .
                                        $comentario . "\n*Por favor no responder este mensaje*.\n"
                                        ."- Sus resultados también están disponibles en el link despues de la imagen:";

                                    $req = $wp->sendBotMessage($mensaje_prs, $envio->instancia);
                                    $public_dir = $service->getDirectory() . $prueba->idpruebaserologicas;

                                    $wp->sendResultadoImage($envio->instancia, $public_dir);
                                    sleep(4);
                                    $wp->sendBotConstanciasLink($envio->instancia);

                                    if ($req['sent']){
                                        $consulta = new ConsultaWhatsAppPrs();
                                        $consulta->idenvio_whatsapp = $envio->idenvio_whatsapp;
                                        $consulta->save();

                                        //File::deleteDirectory($public_dir, false);
                                    }
                                } catch (\Exception $ex) {
                                    //no-op
                                }

                            }
                        }
                    }

                    $enviopcr = EnvioWpPcr::where('numero_celular', $celular[0])
                        ->withCount('consulta')->latest()->first();

                    $dias = $enviopcr->PcrPruebaMolecular->FichaPaciente->Estacion->Sede->idsedes === 4 ? 7 : 1;

                    if ($enviopcr) {
                        $pruebapcr = PcrPruebaMolecular::where('idpcr_pruebas_moleculares', $enviopcr->idpcr_prueba_molecular)
                            ->whereBetween(
                                DB::raw('DATE(created_at)'), [
                                    date('Y-m-d', strtotime("-$dias days")),
                                    date('Y-m-d')
                                ])
                            ->whereHas('FichaPaciente')
                            ->whereNotNull('resultado')
                            ->where('resultado','<>', 2)
                            ->with('FichaPaciente.PacienteIsos')
                            ->first();

                        if ($pruebapcr && $pruebapcr->FichaPaciente->PacienteIsos->numero_documento == $text[0]) {
                            if($enviopcr->consulta_count === 0){
                                //Log::debug($pruebapcr);
                                try {
                                    $pcrService = new PruebaMolecularService($pruebapcr);
                                    //Log::debug($pruebapcr);
                                    $resultado_pcr = $pcrService->whatsAppResult();
                                    $paciente = $pruebapcr->Fichapaciente->PacienteIsos;
                                    $nombre_paciente = $paciente->nombres. " " .$paciente->apellido_paterno. " " . $paciente->apellido_materno;

                                    $mensaje_pcr = '*Este es un mensaje automático*: Sr(a) ' . $nombre_paciente.', el resultado de su prueba molecular del día ' .
                                        $pruebapcr->created_at->format('d-m-Y') . ' es *' . $resultado_pcr['resultado'] . '*. '
                                        . $resultado_pcr['comentario'] . "\n*Por favor no responder este mensaje*.\n"
                                        ."- Sus resultados también están disponibles en el siguiente link:";

                                    $req = $wp->sendBotMessage($mensaje_pcr, $enviopcr->instancia);
                                    $wp->sendBotConstanciasLink($enviopcr->instancia);

                                    if ($req['sent']){
                                        $consulta_pcr = new ConsultaWhatsAppPcr();
                                        $consulta_pcr->id_envio_whatsapp_pcr = $enviopcr->id;
                                        $consulta_pcr->save();
                                    }
                                } catch (\Exception $ex) {
                                    //
                                }

                            }
                        }
                    }

                    $envioAg = EnvioWpAg::where('numero_celular', $celular[0])
                        ->where(DB::raw('DATE(created_at)'), $fechaActual)
                        ->withCount('consulta')->latest()->first();

                    if ($envioAg && $hora < 17 && $envioAg->consulta_count < 1) {
                        try {
                            $agService = new PruebaAntigenaService($envioAg->prueba);
                            $mensaje = '*Este es un mensaje automático*: Sr(a) ' . $envioAg->prueba->ficha->PacienteIsos->full_name.', el resultado de su prueba antígena del día ' .
                                $envioAg->prueba->created_at->format('d-m-Y') . ' es *' . $agService->strResult() . '*. '
                                . "\n*Por favor no responder este mensaje*.\n"
                                ."- Sus resultados también están disponibles en el siguiente link:";

                            $req = $wp->sendBotMessage($mensaje, $envioAg->instancia);
                            $wp->sendBotConstanciasLink($envioAg->instancia);

                            if ($req['sent']){
                                $consultaAg = new ConsultaWhatsappAg();
                                $consultaAg->id_envio_whatsapp_ag = $envioAg->id;
                                $consultaAg->save();
                            }

                        } catch (\Exception $ex) {
                            //
                        }

                    }

                }
            }
        }
    }
}
