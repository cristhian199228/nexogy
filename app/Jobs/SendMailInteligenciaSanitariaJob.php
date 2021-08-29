<?php

namespace App\Jobs;

use App\FichaPaciente;
use App\Mail\MailInteligenciaSanitaria;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendMailInteligenciaSanitariaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var FichaPaciente
     */
    private $ficha;
    private $copia;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(FichaPaciente $ficha, $copia)
    {
        //
        $this->ficha = $ficha;
        $this->copia = $copia;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $destinatarios = [
            'juan.sanchez@internationalsos.com',
            'jquispea2@fmi.com',
            'fzegarra@fmi.com',
            'npacheco@fmi.com',
            'pperalta1@fmi.com'
        ];
        //$destinatarios = ['samuel.larico@isos-peru.com'];
        Mail::to($destinatarios)
            ->cc($this->copia)
            ->send(new MailInteligenciaSanitaria($this->ficha));

    }
}
