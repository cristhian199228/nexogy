<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FichaInvestigacion extends Model
{
    //
    protected $table = 'inv_ficha';
    protected $primaryKey = 'idinv_ficha';

    public function FichaInvCuadroClinico(){
        return $this->hasOne('App\FichaInvCuadroClinico','idinv_ficha','idinv_ficha');
    }
    public function FichaInvDatosNotificacion(){
        return $this->hasOne('App\FichaInvDatosNotificacion','idinv_ficha','idinv_ficha');
    }
    public function FichaInvViajeExposicion(){
        return $this->hasOne('App\FichaInvViajeExposicion','idinv_ficha','idinv_ficha');
    }
    public function FichaInvLaboratorio(){
        return $this->hasOne('App\FichaInvLaboratorio','idinv_ficha','idinv_ficha');
    }
    public function PcrPruebaMolecular(){
        return $this->belongsTo('App\PcrPruebaMolecular','idpcr_prueba_molecular','idpcr_pruebas_moleculares');
    }
    public function FichaInvFoto(){
        return $this->hasOne('App\FichaInvFoto','idinv_ficha','idinv_ficha');
    }
}
