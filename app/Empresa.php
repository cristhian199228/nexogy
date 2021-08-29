<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    //protected $connection = 'mysql';
    protected $table = 'empresa';
    protected $primaryKey = 'idempresa';

    public function FichaPaciente()
    {
        return $this->hasMany('App\FichaPaciente', 'idempresa', 'idempresa');
        //return $this->hasMany('App\Pago','idvecino', 'id');
    }
    public function PacienteIsos() {
        return $this->hasMany('App\PacienteIsos','idempresa','idempresa');
    }
}
