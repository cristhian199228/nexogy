<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FotoEvidenciaRC extends Model
{
    //
    protected $table = "rc_evidencia_foto";

    public function evidencia () {
        return $this->belongsTo('App\EvidenciaRC', 'id_evidencia');
    }
}
