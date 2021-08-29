<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\PacienteIsos;
use App\PcrPruebaMolecular;
use App\FichaPaciente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class RevisarWhatsapp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'revisar:wp_prs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revisa si el cliente recibiò el whatsapp';

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
        $fechaAnterior = date('Y-m-d', strtotime("-1 days"));

        //$hora = date('H');
        $fichas = FichaPaciente::where(DB::raw('DATE(created_at)'), $fechaActual)
            ->where('conforme_prs', 0)
            ->where('enviar_mensaje', 1)
            ->whereHas('PacienteIsos')
            ->whereHas('Estacion', function (Builder $q) {
                $q->whereIn('idsede', [7,3]);
            })

            ->whereHas('PruebaSerologica', function ($q) {
                $q->whereNotNull('p1_react1gm')
                    ->whereNotNull('p1_reactigg')
                    ->whereNotNull('p1_reactigm_igg')
                    ->whereNotNull('invalido');
            })
            //->with('PcrPruebaMolecular')
            //->where('id_paciente', '5624')
            ->with('PacienteIsos:idpacientes,numero_documento,tipo_documento,celular')
            // ->latest()
            //->limit(100)
            ->get();
        //dd($fichas);
        //echo "trajo coleccion";

        foreach ($fichas as $ficha) {
            //echo "empieza ciclo";
            //echo $ficha->PacienteIsos['celular'];


            //$token = '30gu4n20pgznwawl';
            //$instanceId = '266486';
            $token = 'obplrmolunfd1a1a';
            $instanceId = '231706';
            $chatId = '51' . $ficha->PacienteIsos['celular'] . '%40c.us';
            $min_time = strtotime($fechaActual);
            //$min_time = '1624024254';

            $url = 'https://api.chat-api.com/instance' . $instanceId . '/messages?token=' . $token . '&chatId=' . $chatId . '&min_time=' . $min_time;
            //echo $url.'<br>';
            $resultado =  Http::get($url);
            echo "trae los chats";



            //$cadena_buscada   = 'el resultado de su prueba molecular del';
            $cadena_buscada   = 'el resultado de su prueba serológica del dia';
            //dd($resultado['messages']);
            foreach ($resultado['messages'] as $message)  // Echo every message
            {
                echo $message['senderName'];
                $cadena_de_texto = $message['body'];
                $resultado_texto = 'no';
                $existe_foto = 'no';
                //echo $cadena_de_texto;
                $posicion_coincidencia = strpos($cadena_de_texto, $cadena_buscada);
                if ($posicion_coincidencia === false) {
                    if (isset($cadena_de_texto)) {
                        echo 'narnia';
                        // echo $cadena_de_texto;
                    }
                } else {
                    $resultado_texto = 'si';
                    echo 'wow';
                    break;
                }
            }
            /* foreach ($resultado['messages'] as $message2)  // Echo every message
            {
                echo $message2['senderName'];

                if ($message2['type'] == "image" && $message2['self'] == 1) {
                    $existe_foto = 'si';
                    echo 'seee';
                    break;
                } else {
                    //echo 'no hay foto';
                }
            }*/
            if (isset($resultado_texto)) {
                if ($resultado_texto == 'si') {
                    //echo 'si hay';
                    $post = FichaPaciente::find($ficha['idficha_paciente']);
                    $post->conforme_prs = "1";
                    $post->save();
                }
            }
            /* if (isset($resultado_texto) && isset($existe_foto)) {
                if ($resultado_texto == 'si' && $existe_foto == 'si') {
                    //echo 'si hay';
                    $post = FichaPaciente::find($ficha['idficha_paciente']);
                    $post->conforme = "2";
                    $post->save();
                }
                if ($resultado_texto == 'si' && $existe_foto == 'no') {

                    //echo 'si hay';
                    $post = FichaPaciente::find($ficha['idficha_paciente']);
                    $post->conforme = "1";
                    $post->save();
                }
                if ($resultado_texto == 'no' && $existe_foto == 'no') {
                    //echo 'si hay';
                    $post = FichaPaciente::find($ficha['idficha_paciente']);
                    $post->conforme = "0";
                    $post->save();
                }
            }
*/


            /* if ( $resultado_texto == 'si' ) {
                //echo 'si hay';
                $post = FichaPaciente::find($ficha['idficha_paciente']);
                $post->conforme = "2";
                $post->save();
            }*/
        }
        // URL for request GET /messages


        /*$url = 'https://eu219.chat-api.com/instance' . $instanceId . '/messages?token=' . $token . '&chatId=51961498695%40c.us&min_time=1624024254';
        $resultado =  Http::get($url);
        $cadena_buscada   = 'el resultado de su prueba serológica del dia';*/
        //$cadena_buscada   = 'el resultado de su prueba molecular del dia';
        //$resultado_texto = 'si';
        /* foreach ($resultado['messages'] as $message) { // Echo every message
            //echo "Sender:" . $message['author'] . "<br>";
            //echo "Message: " . $message['body'] . "<br>";
            $cadena_de_texto = $message['body'];
            $posicion_coincidencia = strpos($cadena_de_texto, $cadena_buscada);

            if ($posicion_coincidencia === false) {
            } else {
                $resultado_texto = 'si';
                
            }
        }*/
        //$inst = $total >= 10 ? $this->instancia2 : $this->instancia1;
        /*if (isset($resultado_texto)){
            echo $resultado_texto;
        }*/
        //$cadena_de_texto = 'Esta es la frase donde haremos la búsqueda';

        //  $posicion_coincidencia = strpos($cadena_de_texto, $cadena_buscada);

        //se puede hacer la comparacion con 'false' o 'true' y los comparadores '===' o '!=='





        /* foreach ($fichas as $ficha) {
            "51961353921@c.us";
        }
*/
        // print_r($fichas);
    }
}
