<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AntecedentesEp extends Model
{
    protected $table = 'aepidemologicos';
    protected $primaryKey = 'idaepidemologicos';

    protected $fillable = [
        'idfichapacientes',
        'dias_viaje',
        'contacto_cercano',
        'conv_covid',
        'paises_visitados',
        'debilite_sistema',
        'medio_transporte',
        'fecha_llegada',
        'fecha_ultimo_contacto',
        'usuario',
       /* 'embarazo',
        'enfermedad_cardiovascular',
        'diabetes',
        'enfermedad_hepatica',
        'enfermedad_cronica',
        'pos_parto',
        'inmunodeficiencia',
        'enfermedad_renal',
        'dano_hepatico',
        'enfermedad_pulmonar',
        'cancer',
        'condicion_otro',*/
        'id_usuario'
    ];

    public function FichaPaciente() {
        return $this->belongsTo('App\FichaPaciente','idficha_paciente','idfichapacientes');
    }
}
