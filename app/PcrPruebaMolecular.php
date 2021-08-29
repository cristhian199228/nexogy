<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PcrPruebaMolecular extends Model
{
    //
    protected $table = 'pcr_pruebas_moleculares';
    protected $primaryKey = 'idpcr_pruebas_moleculares';

    public function FichaPaciente() {
        return $this->belongsTo('App\FichaPaciente','idficha_paciente','idficha_paciente');
    }

    public function FichaInvestigacion() {
        return $this->hasOne('App\FichaInvestigacion','idpcr_prueba_molecular');
    }

    public function PcrEnvioMunoz() {
        return $this->hasOne('App\PcrEnvioMunoz','idpcr_prueba_molecular','idpcr_pruebas_moleculares');
    }

    public function EnvioWpPcr(){
        return $this->hasOne('App\EnvioWpPcr', 'idpcr_prueba_molecular', 'idpcr_pruebas_moleculares');
    }

    public function PcrFotoMuestra(){
        return $this->hasOne('App\PcrFotoMuestra', 'idpcr_prueba_molecular', 'idpcr_pruebas_moleculares');
    }

    public function PcrEnvioLaboratorio(){
        return $this->belongsTo('App\PcrEnvioLaboratorio','id_envio_laboratorio');
    }

    public function reevaluacion() {
        return $this->hasMany('App\PcrReevaluacion', 'idpcr_prueba_molecular', 'idpcr_pruebas_moleculares');
    }

}
