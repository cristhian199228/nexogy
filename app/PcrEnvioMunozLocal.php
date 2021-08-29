<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PcrEnvioMunozLocal extends Model
{
    //
    protected $connection = 'mysql2';
    protected $table = 'pcr_envio_munoz';
    protected $primaryKey = 'idpcr_envio_munoz';

    public function PcrPruebaMolecular(){
        return $this->belongsTo('App\PcrPruebaMolecular','idpcr_prueba_molecular','idpcr_pruebas_moleculares');
    }
}
