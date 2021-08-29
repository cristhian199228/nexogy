<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\ConsultaWhatsappAg;
use App\ConsultaWhatsAppPcr;
use App\ConsultaWhatsAppPrs;
use App\EnvioWpAg;
use App\MensajeWhatsApp;
use App\FichaPaciente;
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


class EnvioWPAutomatico extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'envio:wpaut';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia resultado Automaticamente pasado un tiempo predeterminado';

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

        $fichas = FichaPaciente::where(DB::raw('DATE(created_at)'), $fechaActual)
            ->where('created_at', '<', now()->subMinutes(20)->toDateTimeString())
            ->where('conforme_prs', '!=', 1)
            ->where('enviar_mensaje', 1)
            ->whereHas('PacienteIsos')
            ->whereHas('Estacion', function (Builder $q) {
                $q->whereIn('idsede', [7,3]);
            })

            ->whereHas('PruebaSerologica', function ($q) {
                $q->whereHas("EnvioWP")
                    ->whereNotNull('p1_react1gm')
                    ->whereNotNull('p1_reactigg')
                    ->whereNotNull('p1_reactigm_igg')
                    ->whereNotNull('invalido');
            })
            ->with(["PruebaSerologica" => function ($q) {
                $q->where("invalido", 0)
                    //->whereNotNull("no_reactivo")
                    ->latest();
            }])
            //->where('id_paciente', '5624')
            ->with('PacienteIsos:idpacientes,numero_documento,tipo_documento,celular')
            // ->latest()
            //->limit(100)
            ->get();
        //dd($fichas);
        //Foo::whereBetween('created_at', [now()->subMinutes(3), now()])->get();
        foreach ($fichas as $ficha) {
            $wp = new MensajeWhatsApp('51' . $ficha->PacienteIsos['celular'], true);
            $chatId = '51' . $ficha->PacienteIsos['celular'] . '@c.us';
            $wp->setChatId($chatId);
            $var = $ficha->PacienteIsos['celular'];
            echo $var;
            $hora = date('H');
            $fechaActual = date('Y-m-d');
            $fechaAnterior = date('Y-m-d', strtotime("-1 days"));




            $prueba = PruebaSerologica::where('idpruebaserologicas', $ficha->PruebaSerologica[0]->idpruebaserologicas)
                ->whereHas('FichaPaciente')
                ->with('FichaPaciente.PacienteIsos', 'FichaPaciente.DatosClinicos', 'FichaPaciente.AntecedentesEp')
                ->whereNotNull('no_reactivo')
                ->first();




            $service = new PruebaSerologicaService($prueba);
            $service->drawAndSaveImage2();
            $resultado_prs = $service->whatsAppResult();
            $paciente = $prueba->Fichapaciente->PacienteIsos;
            $nombre_paciente = $paciente->nombres . " " . $paciente->apellido_paterno . " " . $paciente->apellido_materno;
            $res = $resultado_prs['resultado'];
            $clas = $resultado_prs['clasificacion'];
            $comentario = $resultado_prs['comentario'];

            if (count($prueba->Fichapaciente->DatosClinicos) > 0) {
                $res .= "/PRESENTA SINTOMATOLOGIA";
                $clas = "DEBE PERMANECER EN SALA DE ESPERA";
                $comentario = "En breve será contactado por el Área Médica para generar el proceso complementario. Debe permanecer en la Sala donde le han indicado que espere.";
            }
            if (count($prueba->Fichapaciente->AntecedentesEp) > 0) {
                $res .= "/PRESENTA ANTECEDENTES EPIDEMIOLÓGICOS";
                $clas = "DEBE PERMANECER EN SALA DE ESPERA";
                $comentario = "En breve será contactado por el Área Médica para generar el proceso complementario. Debe permanecer en la Sala donde le han indicado que espere.";
            }

            $mensaje_prs = '*Este es un texto automático-*: Sr(a) ' . $nombre_paciente . ', el resultado de su prueba serológica del dia ' .
                $prueba->created_at->format('d-m-Y') . ' es *' . $res . '*, por lo tanto  *' . $clas . "*. " .
                $comentario . "\n*Por favor no responder este mensaje*.\n"
                . "- Sus resultados también están disponibles en el link despues de la imagen:";

            $req = $wp->sendBotMessage($mensaje_prs, 1);
            $public_dir = $service->getDirectory() . $prueba->idpruebaserologicas;
            echo $public_dir;
            $public_dir2 = 'storage/fotos_temporal/' . $prueba->idpruebaserologicas . '/';


            $wp->sendResultadoImage(1, $public_dir2);
            sleep(4);
            $wp->sendBotConstanciasLink(1);

            /*if ($req['sent']) {
                    $consulta = new ConsultaWhatsAppPrs();
                    $consulta->idenvio_whatsapp = $envio->idenvio_whatsapp;
                    $consulta->save();
                }*/
        }



        return 0;
    }
}
