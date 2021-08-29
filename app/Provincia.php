<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    protected $table = 'gralprovincias';
    protected $primaryKey = 'idprovincia';
    //

    public function Departamento()
    {
        return $this->belongsTo('App\DepartamentoPeru', 'iddepartamento');
        //return $this->BelongsTo('App\Vecino','idvecino');
    }

    public function Distrito()
    {
        return $this->hasMany('App\Provincia', 'iddistrito', 'iddistrito');
        //return $this->hasMany('App\Pago','idvecino', 'id');
    }
}
