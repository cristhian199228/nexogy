<?php

namespace App\Console\Commands;

use App\EnvioWpPcr;
use App\FichaPaciente;
use App\Service\PacienteService;
use App\Service\PruebaMolecularService;
use App\Service\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnviarWhatsAppPcr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enviar:wp_pcr';

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
        $fechaAnterior = date('Y-m-d', strtotime("-4 days"));
        $hora = date('H');

        //if ($hora >= 17 || $hora <= 7) {

            $fichas =  FichaPaciente::whereBetween(DB::raw('DATE(created_at)'), [$fechaAnterior, $fechaActual])
                ->whereHas('PcrPruebaMolecular', function (Builder $q){
                    $q->whereNotNull('resultado')->doesntHave('EnvioWpPcr');
                })->where('enviar_mensaje', 1)
                ->take(20)
                ->get();

            //dd($fichas->count());

            foreach ($fichas as $ficha) {
                $cel = $ficha->PacienteIsos->celular;
                $wp = new WhatsAppService($cel);
                try {
                    $pcrService = new PruebaMolecularService($ficha->PcrPruebaMolecular);
                    $pacienteService = new PacienteService($ficha->PacienteIsos);
                    $resultado_pcr = $pcrService->whatsAppResult();
                    $paciente = $ficha->PcrPruebaMolecular->Fichapaciente->PacienteIsos;

                    $mensaje_pcr = '*Este es un mensaje automático*: Sr(a) ' . $paciente->full_name.', el resultado de su prueba molecular del día ' .
                        $ficha->PcrPruebaMolecular->created_at->format('d-m-Y') . ' es *' . $resultado_pcr['resultado'] . '*. '
                        . $resultado_pcr['comentario'] . "\n*Por favor no responder este mensaje*.\n"
                        ."- Sus resultados también están disponibles en el siguiente link:\n".
                        CONSTANCIAS_URL;

                    if (!$pacienteService->esPositivoPcrTerceraVez()) {
                        $req = $wp->sendMessage($mensaje_pcr);

                        if(isset($req['request']['sent']) && $req['request']['sent']) {
                            $envio = new EnvioWpPcr();
                            $envio->idpcr_prueba_molecular = $ficha->PcrPruebaMolecular->idpcr_pruebas_moleculares;
                            $envio->numero_celular = "51" .$cel;
                            $envio->id_instancia = $req['instancia'];
                            $envio->estado = 1;
                            $envio->save();
                        }
                    }
                } catch (\Exception $ex) {

                }
            }

        return 0;
    }
}
