<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    //
    protected $table = 'sedes';
    protected $primaryKey = 'idsedes';

    public function Estacion()
    {
        //return $this->hasMany('App\Comment');
        return $this->hasMany('App\Estacion','idsede','idsedes'); 
    }

}
