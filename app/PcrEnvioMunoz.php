<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PcrEnvioMunoz extends Model
{
    //
    protected $table = 'pcr_envio_munoz';
    protected $primaryKey = 'idpcr_envio_munoz';

    public function PcrPruebaMolecular(){
        return $this->belongsTo('App\PcrPruebaMolecular','idpcr_prueba_molecular','idpcr_pruebas_moleculares');
    }

    public function consumo() {
        return $this->hasMany('App\PcrConsumoMunoz', 'idpcr_envio_munoz', 'idpcr_envio_munoz');
    }
}
