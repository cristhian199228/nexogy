<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SoapClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\LlamadaNexogy;
use DateTimeZone;
use DateTime;
use DateInterval;
use ErrorException;


class DescargarNexogy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'descargar:nexogy';

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
        $customerid = '7a181d99-d3fd-4a5e-9a23-413f4ba3159e';
        $apikey = 'a3c5db42-2fc5-4b26-ae67-667d1f4625bb';
        $siteid = '55931d42-f510-4342-a790-93e45a8f9b27';

        //$fechainicial = '2020-07-31 00:00:00';
        //$fechafinal = '2020-07-31 00:30:00';

        $format = 'Y-m-d H:i:s';

        $fechainicial = DateTime::createFromFormat($format, '2020-07-31 00:00:00');
        //dd($fechainicial);
        $fechafinal = DateTime::createFromFormat($format, '2020-08-01 00:00:00');
        //dd($fechafinal);
        $diff = $fechainicial->diff($fechafinal);
        //dd($diff);
        $resta = ((($diff->days * 60 * 24) + $diff->h * 60 + $diff->i) / 30);
        $aumentohora = 'PT30M';

        for ($i = 1; $i <= $resta; $i++) {

            if ($i == 1) {

                $fechainicial_f = $fechainicial->format('Y-m-d\TH:i:s'); //17:00:00
                $fechafinal_f = $fechainicial->add(new DateInterval($aumentohora));
                $fechafinal_f = $fechafinal_f->format('Y-m-d\TH:i:s'); //17:00:00
                // echo 'finicio : '.$fechainicial_f . '-- ffinal :' . $fechafinal_f . '-------';
            } else {

                // $fechainicial = $fechainicial->add(new DateInterval($aumentohora));
                //dd($fechainicial);
                $fechainicial_f = $fechainicial->format('Y-m-d\TH:i:s'); //17:00:00
                $fechafinal = $fechainicial->add(new DateInterval($aumentohora));
                //dd($fechainicial_f);

                $fechafinal_f = $fechafinal->format('Y-m-d\TH:i:s'); //17:00:00
                /*$fechainicial_f = new DateTime($fechainicial_f);
                $fechafinal_f = $fechainicial_f->add(new DateInterval($aumentohora));

                $fechafinal_f = $fechafinal_f->format('Y-m-d\TH:i:s'); //17:00:00
                $fechainicial_f = $fechainicial->format('Y-m-d\TH:i:s'); //17:00:00*/
            }
            $params = array(
                "CustomerID" => $customerid,
                "APIKey" =>  $apikey,
                "SiteID" =>  $siteid,
                //'FromDateTime' => now(),

                'FromDateTime' => $fechainicial_f,
                'ToDateTime' => $fechafinal_f,
                /* 'FromDateTime' => '2021-08-21T00:00:00',
                'ToDateTime' => '2021-08-21T00:30:00',*/
                //'FromDuration' => '55931d42-f510-4342-a790-93e45a8f9b27',
                //'ToDuration' => '55931d42-f510-4342-a790-93e45a8f9b27',
                //'AgentName' => '55931d42-f510-4342-a790-93e45a8f9b27',
                //'Extension' => '55931d42-f510-4342-a790-93e45a8f9b27',
                //'PhoneNumber' => '55931d42-f510-4342-a790-93e45a8f9b27',
                //'isIncoming' => 'true',
                /*'CustomerInternalRef' => '55931d42-f510-4342-a790-93e45a8f9b27',
                'DiD' => '55931d42-f510-4342-a790-93e45a8f9b27',
                'isFlagged' => '55931d42-f510-4342-a790-93e45a8f9b27',
                'CallerID' => '55931d42-f510-4342-a790-93e45a8f9b27',
                'PhoneSystemCallIDStartsWith' => '55931d42-f510-4342-a790-93e45a8f9b27',
                'PhoneSystemCallIDContains' => '55931d42-f510-4342-a790-93e45a8f9b27',*/
            );
            //https: //secure.callcabinet.com/APIServices/DownloadAudioFile?APIKey=a3c5db42-2fc5-4b26-ae67-667d1f4625bb&CustomerID=7a181d99-d3fd-4a5e-9a23-413f4ba3159e&SiteID=55931d42-f510-4342-a790-93e45a8f9b27&CallID=44531b4a-5ce2-eb11-a7ad-281878ce9766

            /*
        https://secure.callcabinet.com/APIServices/DownloadAudioFile?APIKey=BD833A12-5312-4EEA-A8AE-20C791981A01&CustomerID=AEF3FAE6-EB7E-4F77-A9B0-8C6FF0AFFDD5&SiteID=B00664FC-D3ED-437A-B358-3FE7CFC253B6&CallID=70c08bc6-4b2e-4f82-9684-cb83508c458d
        */
            $client = new SoapClient('https://secure.callcabinet.com/APIServices/CallListingService.svc?wsdl');
            $respuesta_ws = $client->GetListOfCallsWithSearch($params);
            //dd($respuesta_ws);
            //$response = file_get_contents('https://api.callcabinet.com/api/CallListingService');
            $customerid = '7a181d99-d3fd-4a5e-9a23-413f4ba3159e';
            $apikey = 'a3c5db42-2fc5-4b26-ae67-667d1f4625bb';
            $siteid = '55931d42-f510-4342-a790-93e45a8f9b27';
            //dd($respuesta_ws->GetListOfCallsWithSearchResult);
            if (isset($respuesta_ws->GetListOfCallsWithSearchResult->CallListingAPIEntry)) {

                $llamadas = $respuesta_ws->GetListOfCallsWithSearchResult->CallListingAPIEntry;
                // dd($llamadas);
                if (is_array($llamadas)) {

                    echo count($llamadas) . '--';
                    foreach ($llamadas as $llamada) {
                        //echo($llamada->CallId);
                        if (LlamadaNexogy::where('CallId', '=', $llamada->CallId)->exists()) {
                            echo 'usuario encontrado  es array';
                        } else {
                            $contents = file_get_contents('https://secure.callcabinet.com/APIServices/DownloadAudioFile?APIKey=' . $apikey . '&CustomerID=' . $customerid . '&SiteID=' . $siteid . '&CallID=' . $llamada->CallId);
                            Storage::disk('ftp')->put('/NEXOGY/' . $llamada->CallId . '.wav', $contents);
                            $foto = new LlamadaNexogy();
                            $foto->AgentName = $llamada->AgentName;
                            $foto->CallId = $llamada->CallId;
                            $foto->CallerId = $llamada->CallerID;
                            $foto->CustomerInternalRef = $llamada->CustomerInternalRef;
                            $foto->DTMF = $llamada->DTMF;
                            $foto->DiD = $llamada->DiD;
                            $foto->Direction = $llamada->Direction;
                            $foto->Duration = $llamada->Duration;
                            $foto->Extension = $llamada->Extension;
                            $foto->Flagged = $llamada->Flagged;
                            $foto->PhoneNumber = $llamada->PhoneNumber;
                            $foto->RecordingAvailable = $llamada->RecordingAvailable;
                            $foto->StartTime = $llamada->StartTime;
                            $foto->save();
                            //Storage::put($llamada->CallId . '.wav', $contents);
                        }
                    }
                } else {
                    echo 'no es array --';
                    if (LlamadaNexogy::where('CallId', '=', $llamadas->CallId)->exists()) {
                        echo 'usuario encontrado no es array';
                    } else {
                        $contents = file_get_contents('https://secure.callcabinet.com/APIServices/DownloadAudioFile?APIKey=' . $apikey . '&CustomerID=' . $customerid . '&SiteID=' . $siteid . '&CallID=' . $llamadas->CallId);
                        //echo($llamada->CallId);

                        Storage::disk('ftp')->put('/NEXOGY/' . $llamadas->CallId . '.wav', $contents);
                        $foto = new LlamadaNexogy();
                        $foto->AgentName = $llamadas->AgentName;
                        $foto->CallId = $llamadas->CallId;
                        $foto->CallerId = $llamadas->CallerID;
                        $foto->CustomerInternalRef = $llamadas->CustomerInternalRef;
                        $foto->DTMF = $llamadas->DTMF;
                        $foto->DiD = $llamadas->DiD;
                        $foto->Direction = $llamadas->Direction;
                        $foto->Duration = $llamadas->Duration;
                        $foto->Extension = $llamadas->Extension;
                        $foto->Flagged = $llamadas->Flagged;
                        $foto->PhoneNumber = $llamadas->PhoneNumber;
                        $foto->RecordingAvailable = $llamadas->RecordingAvailable;
                        $foto->StartTime = $llamadas->StartTime;
                        $foto->save();
                        //Storage::put($llamada->CallId . '.wav', $contents);
                    }
                }
            } else {
                echo 'vacio';
            }

           // echo 'finicio : ' . $fechainicial_f . '-- f.final' . $fechafinal_f . '-------';
        }



        return 0;
    }
}
