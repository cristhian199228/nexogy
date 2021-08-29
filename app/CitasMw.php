<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CitasMw extends Model
{
    //
    protected $table = 'citas_mw';
    protected $primaryKey = 'idcitas_mw';

    public function FichaPaciente() {
        return $this->belongsTo('App\FichaPaciente','idficha_paciente','idfichapacientes');
    }
}
