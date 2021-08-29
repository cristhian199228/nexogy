<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConsultaWhatsAppPrs extends Model
{
    //
    protected $table = "consulta_paciente_wp_prs";

    public function EnvioWP(){
        return $this->belongsTo('App\EnvioWP','idenvio_whatsapp', 'idenvio_whatsapp');
    }
}
