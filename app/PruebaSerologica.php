<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PruebaSerologica extends Model
{
    protected $table = 'pruebaserologicas';
    protected $primaryKey = 'idpruebaserologicas';
    protected $fillable = [
        'idfichapacientes', 'ps_llamada_113', 'ps_contactocasocon', 'ps_contactocasosos', 'ps_personaext', 'ps_personalsalud', 'ps_otro',
        'p1_react1gm', 'p1_reactigg', 'p1_reactigm_igg', 'invalido', 'ccs', 'condicion_riesgo', 'condicion_riesgo_detalle', 'ps_de_eess',
        'no_reactivo', 'hora_inicio', 'hora_fin', 'p1_positivo_recuperado', 'p1_marca', 'usuario', 'codigo_ps', 'positivo_anterior',
        'fecha_positivo_anterior', 'lugar_positivo_anterior', 'p1_positivo_persistente','p1_positivo_vacunado','id_usuario'
    ];

    protected $casts = [
        'ccs' => 'int',
        'p1_marca' => 'int'
    ];

    public function Fichapaciente()
    {
        return $this->belongsTo('App\FichaPaciente','idfichapacientes', 'idficha_paciente');
    }
    public function EnvioWP() {
        return $this->hasMany('App\EnvioWP','idpruebaserologicas','idpruebaserologicas' );
    }

    public function reevaluacion() {
        return $this->hasMany('App\PrsReevaluacion','idpruebaserologicas','idpruebaserologicas' );
    }
}
