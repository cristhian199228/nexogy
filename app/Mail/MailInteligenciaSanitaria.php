<?php

namespace App\Mail;

use App\FichaPaciente;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailInteligenciaSanitaria extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = 'XML Reevaluación';
    /**
     * @var FichaPaciente
     */
    private $ficha;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(FichaPaciente $ficha)
    {
        //
        $this->ficha = $ficha;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $ruta = "app/xml/". $this->ficha->idficha_paciente . ".xml";
        $location = storage_path($ruta);
        return $this->view('mail.envio_xml_is')->with('full_name', $this->ficha->PacienteIsos->full_name)->attach($location);
    }
}
