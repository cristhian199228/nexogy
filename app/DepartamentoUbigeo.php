<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DepartamentoUbigeo extends Model
{
    //
    protected $table = "ubigeo_peru_departments";
    protected $keyType = 'string';
    public $timestamps = false;

    public function PacienteIsos() {
        return $this->hasMany("App\PacienteIsos","residencia_departamento");
    }
}
