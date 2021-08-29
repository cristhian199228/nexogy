<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FichaEpidemiologicaContactoRC extends Model
{
    //

    protected $table = "rc_evidencia_fe_contacto";

    public function fichaEp () {
        return $this->belongsTo('App\FichaEpidemiologicaRC', 'id_evidencia_fe');
    }
}
