<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnvioXMLPcrMina extends Mailable
{
    use Queueable, SerializesModels;
    public $subject = "XML PROCESO DE PCR SEDE MINA";
    public $msg;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($msg)
    {
        //
        $this->msg = $msg;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $datos=$this->msg[0];
        $ruta = "app/xmlpcrmina/$datos.xml";
        $location = storage_path($ruta);

        return $this->view('mail.envioXMLPcrMina')->attach($location);
    }
}
