<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EnvioWpAg extends Model
{
    //
    protected $table = 'envio_whatsapp_ag';

    public function prueba() {
        return $this->belongsTo('App\PruebaAntigena', 'id_prueba_antigena');
    }

    public function consulta() {
        return $this->hasMany('App\ConsultaWhatsappAg', 'id_envio_whatsapp_ag');
    }
}
