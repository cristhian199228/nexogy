<?php

namespace App\Mail;

use App\PcrEnvioCorreo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailEnvioPcrContratistas extends Mailable
{
    use Queueable, SerializesModels;
    public $subject = "Resultados pruebas moleculares empresas contratistas";
    public $msg;
    private $nro_procesados;
    private $total;
    private $nro_positivos;
    private $fecha_referencia;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($msg, $total, $nro_procesados, $nro_positivos, $fecha_referencia)
    {
        //
        $this->msg = $msg;
        $this->total = $total;
        $this->nro_procesados = $nro_procesados;
        $this->nro_positivos = $nro_positivos;
        $this->fecha_referencia = $fecha_referencia;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $fechaActual = date('Y-m-d');
        $horaActual = date('H:i:s');
        $datos = array(
            'fecha_referencia' => $this->fecha_referencia,
            'total' => $this->total,
            'nro_procesados' => $this->nro_procesados,
            'nro_positivos' => $this->nro_positivos,
            'mensaje' => ""
        );

        $ultimo_correo = PcrEnvioCorreo::where('proceso_finalizado', 1)
            ->where('tipo', 2)
            ->where('fecha_referencia', $datos['fecha_referencia'])
            ->latest()
            ->first();

        if(!$ultimo_correo) {

            $envio_correo = new PcrEnvioCorreo();
            $envio_correo->tipo = 2;
            $envio_correo->proceso_finalizado = 0;
            $envio_correo->fecha_referencia = $datos['fecha_referencia'];
            $envio_correo->fecha = $fechaActual;
            $envio_correo->hora = $horaActual;
            $envio_correo->save();

            if($datos['nro_positivos'] > 0) {

                $ruta = "app/excel/resultados_pcr_con.xlsx";
                $location = storage_path($ruta);
                return $this->view('mail.envio_pcr_contratistas')->with('datos', $datos)->attach($location);

            } else {

                $datos["mensaje"] = "No existen resultados positivos contratistas";
                return $this->view('mail.envio_pcr_contratistas')->with('datos', $datos);
            }
        }


    }
}
