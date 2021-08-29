<?php

namespace App\Jobs;

use App\EnvioWpEvidencia;
use App\EvidenciaRC;
use App\PacienteIsos;
use App\Service\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsappEvidenciasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var EvidenciaRC
     */
    private $evidencia;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(EvidenciaRC $evidencia)
    {
        $this->evidencia = $evidencia;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $celular = $this->evidencia->paciente->celular;
        $wp = new WhatsAppService($celular);
        $message = "*Este es un mensaje automático:* Bienvenido al servicio de Response Center de International SOS " .
            "para Sociedad Minera Cerro Verde, por favor ingrese al siguiente link para subir sus documentos y/o fotos.";
        $req = $wp->sendMessage($message);
        if (isset($req['request']['sent']) && $req['request']['sent']) {
            $envio = new EnvioWpEvidencia();
            $envio->id_instancia = $req['instancia'];
            $envio->id_evidencia = $this->evidencia->id;
            $envio->numero_celular = "51" . $celular;
            $envio->estado = 1;
            $envio->save();

            $wp->sendEvidenciasLink();
            $wp->sendManualEvidencias();
            $wp->sendVideoEvidencias();
        }
    }
}
