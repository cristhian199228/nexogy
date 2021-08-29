<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PrsReevaluacion extends Model
{
    //
    protected $table = 'prs_reevaluacion';

    public function pruebaSerologica() {
        return $this->belongsTo('App\PruebaSerologica', 'idpruebaserologicas','idpruebaserologicas');
    }
}
