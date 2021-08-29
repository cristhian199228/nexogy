<?php

namespace App\Jobs;

use App\EnvioWP;
use App\PruebaSerologica;
use App\Service\PruebaSerologicaService;
use App\Service\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsappPrs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var PruebaSerologica
     */
    private $ps;
    /**
     * @var EnvioWP
     */
    private $envio;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(PruebaSerologica $ps, EnvioWP $envio)
    {
        //
        $this->ps = $ps;
        $this->envio = $envio;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $wp = new WhatsAppService($this->ps->FichaPaciente->PacienteIsos->celular);
        $service = new PruebaSerologicaService($this->ps);

        $resultado_prs = $service->whatsAppResult();
        $paciente = $this->ps->Fichapaciente->PacienteIsos;

        $mensaje_prs = '*Este es un mensaje automático*: Sr(a) ' . $paciente->full_name .', el resultado de su prueba serológica del dia ' .
            $this->ps->created_at->format('d-m-Y') . ' es *' . $resultado_prs['resultado'] . '*, por lo tanto  *'. $resultado_prs['clasificacion'] . "*.\n" .
            $resultado_prs['comentario'] . "\n*Por favor no responder este mensaje*.\n"
            ."- Sus resultados también están disponibles en el siguiente link: \n" .
            CONSTANCIAS_URL;

        if ($this->ps->FichaPaciente->Estacion->Sede->idsedes !== 5) {
            $service->saveImageQueue();
            $req  = $wp->sendResultadoImage($service->getStickerShortPath(), $mensaje_prs);
        } else {
            $req = $wp->sendMessage($mensaje_prs);
        }
        if (isset($req['request']['sent']) && $req['request']['sent']) {
            $this->envio->update([
                'id_instancia' => $req['instancia'],
                'estado' => 1
            ]);
        }
    }
}
