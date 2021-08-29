<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FichaCamRC extends Model
{
    //
    protected $table = 'rc_evidencia_fc';

    public function evidencia () {
        return $this->belongsTo('App\EvidenciaRC', 'id_evidencia');
    }
}
