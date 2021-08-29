<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FichaTemporal extends Model
{
    //
    protected $table = "ficha_paciente_temporal";

    protected $guarded = [];

    protected $casts = [
        'tos' => 'boolean',
        'dolor_garganta' => 'boolean',
        'dolor_abdominal' => 'boolean',
        'dolor_articulaciones' => 'boolean',
        'dolor_muscular' => 'boolean',
        'dolor_pecho' => 'boolean',
        'congestion_nasal' => 'boolean',
        'dificultad_respiratoria' => 'boolean',
        'fiebre' => 'boolean',
        'malestar_general' => 'boolean',
        'diarrea' => 'boolean',
        'nauseas_vomitos' => 'boolean',
        'cefalea' => 'boolean',
        'irritabilidad_confusion' => 'boolean',
        'anosmia_ausegia' => 'boolean',
        'viajo_14dias' => 'boolean',
        'tuvo_contacto_cercano' => 'boolean',
        'tuvo_conversacion' => 'boolean',
        'embarazo' => 'boolean',
        'enfermedad_cardiovascular' => 'boolean',
        'diabetes' => 'boolean',
        'enfermedad_hepatica' => 'boolean',
        'enfermedad_cronica' => 'boolean',
        'pos_parto' => 'boolean',
        'inmunodeficiencia' => 'boolean',
        'enfermedad_renal' => 'boolean',
        'dano_hepatico' => 'boolean',
        'enfermedad_pulmonar' => 'boolean',
        'cancer' => 'boolean',
        'estado' => 'boolean',
        'hospitalizado' => 'boolean',
        'falta_aliento' => 'boolean',
        'viajo_14dias_sintomas' => 'boolean',
        'visito_eess' => 'boolean',
        'contacto_infeccion_respiratoria' => 'boolean',
        'contacto_caso_confirmado' => 'boolean',
        'visito_mercado' => 'boolean',
        'positivo_anterior' => 'boolean',
        'recibio_vacuna' => 'boolean',
        'envio_mensaje' => 'boolean'
    ];

    public function paciente() {
        return $this->belongsTo('App\PacienteIsos', 'id_paciente', 'idpacientes');
    }
}
