<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FichaInvFormato extends Model
{
    //
    protected $table = "inv_ficha_formato";

    public function ficha () {
        return $this->belongsTo('App\FichaInvestigacion', 'idinv_ficha', 'idinv_ficha');
    }
}
