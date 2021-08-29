<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FichaEpidemiologicaRC extends Model
{
    //
    protected $table = "rc_evidencia_fe";

    public function evidencia () {
        return $this->belongsTo('App\EvidenciaRC', 'id_evidencia');
    }

    public function contactos () {
        return $this->hasMany('App\FichaEpidemiologicaContactoRC', 'id_evidencia_fe');
    }
}
