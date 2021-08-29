<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Distrito extends Model
{
    //
    protected $table = 'graldistritos';
    protected $primaryKey = 'iddistrito';

    public function Provincia()
    {
        return $this->belongsTo('App\Provincia', 'idprovincia');
        //return $this->BelongsTo('App\Vecino','idvecino');
    }

    public function FichaPaciente()
    {
        return $this->hasMany('App\FichaPaciente', 'idprovincia', 'idprovincia');
        //return $this->hasMany('App\Pago','idvecino', 'id');
    }

}
