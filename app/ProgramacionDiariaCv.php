<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProgramacionDiariaCv extends Model
{
    //
    protected $table = 'programacion_diaria_cv';
    protected $fillable = [
        'fecha',
        'nombres',
        'tipo_documento',
        'numero_documento',
        'registro',
        'empresa',
        'puesto',
        'rol',
        'ruc',
        'turno',
        'prueba',
    ];

    public function paciente() {
        return $this->belongsTo('App\PacienteIsos', 'numero_documento', 'numero_documento');
    }
}
