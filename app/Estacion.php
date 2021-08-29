<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Estacion extends Model
{
    //
    protected $table = 'estaciones';
    protected $primaryKey = 'idestaciones';

    public function Sede()
    {
        return $this->belongsTo('App\Sede','idsede');
    }
    public function FichaPaciente()
    {
        return $this->hasMany('App\FichaPaciente', 'id_estacion', 'idestaciones');
    }

    public function evidencia() {
        return $this->hasMany('App\EvidenciaRC', 'id_estacion', 'idestaciones');
    }
}
