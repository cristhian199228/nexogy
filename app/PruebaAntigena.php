<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PruebaAntigena extends Model
{
    //
    protected $table = 'pruebas_antigenas';
    protected $fillable = [
        'idficha_paciente',
        'llamada_113',
        'prueba_eess',
        'personal_salud',
        'contacto_caso_confirmado',
        'contacto_caso_sospechoso',
        'persona_extranjero',
        'persona_conglomerados',
        'otros',
        'clasificacion_clinica_severidad',
        'condicion_riesgo',
        'condicion_riesgo_otro',
        'marca_prueba',
        'tipo_muestra',
        'tipo_lectura',
        'observaciones',
        'resultado',
        'usuario',
        'started_at',
        'finished_at',
        'id_usuario'
    ];

    protected $casts = [
        'llamada_113' => 'boolean',
        'prueba_eess' => 'boolean',
        'personal_salud' => 'boolean',
        'contacto_caso_confirmado' => 'boolean',
        'contacto_caso_sospechoso' => 'boolean',
        'persona_extranjero' => 'boolean',
        'persona_conglomerados' => 'boolean',
    ];

    public function ficha() {
        return $this->belongsTo('App\FichaPaciente', 'idficha_paciente', 'idficha_paciente');
    }

    public function envio() {
        return $this->hasMany('App\EnvioWpAg', 'id_prueba_antigena');
    }
}
