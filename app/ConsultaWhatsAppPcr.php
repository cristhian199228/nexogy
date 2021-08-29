<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConsultaWhatsAppPcr extends Model
{
    //
    protected $table = "consulta_paciente_wp_pcr";

    public function EnvioWpPcr(){
        return $this->belongsTo('App\EnvioWpPcr', 'id_envio_whatsapp_pcr');
    }
}
