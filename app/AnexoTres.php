<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnexoTres extends Model
{
    //
    protected $table = 'anexotres';
    public function Fichapaciente()
    {
        return $this->belongsTo('App\Fichapaciente','idfichapacientes');
        //return $this->BelongsTo('App\Vecino','idvecino');
    }
}
