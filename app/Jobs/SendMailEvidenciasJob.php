<?php

namespace App\Jobs;

use App\Mail\EvidenciaMail;
use App\PacienteIsos;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMailEvidenciasJob implements ShouldQueue
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
        Mail::to([$this->paciente->correo])
            ->send(new EvidenciaMail());
    }
}
