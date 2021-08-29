<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FichaInvFoto extends Model
{
    //
    protected $table = "inv_ficha_foto";
    protected $primaryKey = "idinv_ficha_foto";

    public function FichaInvestigacion(){
        return $this->belongsTo('App\FichaInvestigacion','idinv_ficha','idinv_ficha');
    }
}
