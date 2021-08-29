<?php

namespace App\Mail;

use App\Exports\ExportExcelPcrMina;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class MailEnvioPcrMina extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = "Resultados pruebas moleculares SEDE SMCV";
    private $fichas;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($fichas)
    {
        //
        $this->fichas = $fichas;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->fichas->count() > 0) {
            $path = 'excel/resultados_pcr_mina.xlsx';
            Excel::store(new ExportExcelPcrMina($this->fichas), $path);
            $excel = storage_path("app/$path");
            return $this->view('mail.envio_pcr_mina')->attach($excel);
        }
        return $this->view('mail.envio_pcr_mina');
    }
}
