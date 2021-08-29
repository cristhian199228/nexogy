<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PcrFotoMuestra extends Model
{
    //
    protected $table = "pcr_foto_muestra";
    protected $fillable = ['idpcr_prueba_molecular', 'path', 'detalle'];

    public function PcrPruebaMolecular(){
        return $this->belongsTo('App\PcrPruebaMolecular', 'idpcr_prueba_molecular', 'idpcr_pruebas_moleculares');
    }
}
