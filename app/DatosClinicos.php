<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DatosClinicos extends Model
{

    protected $table = 'datoclinicos';
    protected $primaryKey = 'iddatoclinicos';

    protected $fillable = [
        'idfichapacientes',
        'tos',
        'dolor_garganta',
        'dificultad_respiratoria',
        'fiebre',
        'malestar_general',
        'diarrea',
        'anosmia_ausegia',
        'otros',
        'toma_medicamento',
        'nauseas_vomitos',
        'congestion_nasal',
        'cefalea',
        'irritabilidad_confusion',
        'falta_aliento',
        'usuario',
        'fecha_inicio_sintomas',
        'dolor_muscular',
        'dolor_abdominal',
        'dolor_articulaciones',
        'dolor_pecho',
        'post_vacunado',
        'id_usuario'
    ];

    public function Fichapaciente(){
        return $this->belongsTo('App\FichaPaciente','idficha_paciente','idfichapacientes');
    }
}
