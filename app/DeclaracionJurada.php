<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeclaracionJurada extends Model
{
    //
    protected $table = 'declaracionesjuradas';
    public function Fichapaciente()
    {
        return $this->belongsTo('App\Fichapaciente','idfichapacientes');
        //return $this->BelongsTo('App\Vecino','idvecino');
    }
}
