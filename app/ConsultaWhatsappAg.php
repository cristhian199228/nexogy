<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConsultaWhatsappAg extends Model
{
    //
    protected $table = 'consulta_paciente_wp_ag';

    public function envio() {
        return $this->belongsTo('App\EnvioWpAg', 'id_envio_whatsapp_ag');
    }
}
