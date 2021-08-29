<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PcrEnvioLaboratorio extends Model
{
    //
    protected $table = 'pcr_envio_laboratorio';
    protected $fillable = ['emisor','receptor','estado','firma_emisor','firma_receptor'];

    public function PcrPruebaMolecular(){
        return $this->hasMany('App\PcrPruebaMolecular','idpcr_pruebas_moleculares');
    }
}
