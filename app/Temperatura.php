<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Temperatura extends Model
{
    protected $table = 'temperaturas';
    protected $primaryKey = 'idtemperatura';

    public function Fichapaciente()
    {
        return $this->belongsTo('App\Fichapaciente','idficha_paciente','idfichapacientes');
    }
}
