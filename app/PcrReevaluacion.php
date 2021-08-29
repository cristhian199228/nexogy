<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PcrReevaluacion extends Model
{
    //
    protected $table = "pcr_reevaluacion";

    public function pruebaMolecular() {
        return $this->belongsTo('App\PcrPruebaMolecular','idpcr_prueba_molecular', 'idpcr_pruebas_moleculares');
    }
}
