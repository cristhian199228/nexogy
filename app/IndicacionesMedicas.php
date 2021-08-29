<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IndicacionesMedicas extends Model
{
    //
    protected $table = "rc_indicaciones_medicas";
    protected $fillable = [
        'id_evidencia',
        'fecha_inicio',
        'dias_descanso',
        'firma_doctor',
        'firma_paciente',
        'usuario',
        'descr_espvalorada',
        'nombre_doctor'
    ];

    public function evidencia() {
        return $this->belongsTo('App\EvidenciaRC', 'id_evidencia');
    }
}
