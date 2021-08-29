<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DistritoUbigeo extends Model
{
    protected $table = "ubigeo_peru_districts";
    protected $keyType = 'string';
    public $timestamps = false;

    public function PacienteIsos() {
        return $this->hasMany("App\PacienteIsos","residencia_distrito");
    }
}
