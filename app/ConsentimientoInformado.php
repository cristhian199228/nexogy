<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConsentimientoInformado extends Model
{
    protected $table = 'consentimientoinformados';
    
    public function Fichapaciente()
    {
        return $this->belongsTo('App\Fichapaciente','idfichapacientes');
        //return $this->BelongsTo('App\Vecino','idvecino');
    }
}
