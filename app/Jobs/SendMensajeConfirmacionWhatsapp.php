<?php

namespace App\Jobs;

use App\EnvioWpConfirmacion;
use App\PacienteIsos;
use App\Service\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMensajeConfirmacionWhatsapp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var PacienteIsos
     */
    private $paciente;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(PacienteIsos $paciente)
    {
        //
        $this->paciente = $paciente;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $wp = new WhatsAppService($this->paciente->celular);

        $fecha = now()->format('d-m-Y');
        $hora = now()->format('H:i');
        $mensaje = "MENSAJE DE COMPROBACIÓN\n" .
            "DNI: " . $this->paciente->numero_documento . "\n".
            "FECHA: $fecha\n".
            "HORA: $hora";

        $req = $wp->sendMessage($mensaje);

        if (isset($req['request']['sent']) && $req['request']['sent']) {
            $envio = new EnvioWpConfirmacion();
            $envio->id_paciente = $this->paciente->idpacientes;
            $envio->id_instancia = $req['instancia'];
            $envio->numero_celular = "51" . $this->paciente->celular;
            $envio->estado = 1;
            $envio->save();
        }
    }
}
