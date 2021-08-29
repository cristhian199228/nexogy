<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EvidenciaRC extends Model
{
    //
    protected $table = "rc_evidencia";
    protected $casts = [
        'estado' => 'boolean',
        'puede_subir_fotos' => 'boolean',
    ];

    public function paciente() {
        return $this->belongsTo('App\PacienteIsos', 'id_paciente');
    }

    public function fotos() {
        return $this->hasMany('App\FotoEvidenciaRC', 'id_evidencia');
    }

    public function fichaEp() {
        return $this->hasOne('App\FichaEpidemiologicaRC', 'id_evidencia');
    }

    public function fichaCam() {
        return $this->hasOne('App\FichaCamRC','id_evidencia');
    }

    public function estacion() {
        return $this->belongsTo('App\Estacion', 'id_estacion','idestaciones');
    }

    public function indicaciones() {
        return $this->hasOne('App\IndicacionesMedicas', 'id_evidencia');
    }
}
