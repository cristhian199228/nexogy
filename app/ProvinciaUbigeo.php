<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProvinciaUbigeo extends Model
{
    protected $table = "ubigeo_peru_provinces";
    protected $keyType = 'string';
    public $timestamps = false;

    public function PacienteIsos() {
        return $this->hasMany("App\PacienteIsos","residencia_provincia");
    }
}
