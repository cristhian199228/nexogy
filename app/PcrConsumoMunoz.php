<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PcrConsumoMunoz extends Model
{
    //
    protected $table = 'pcr_consumo_munoz';
    protected $primaryKey = 'idpcr_consumo_munoz';

    public function envio() {
        return $this->belongsTo('App\PcrEnvioMunoz','idpcr_envio_munoz','idpcr_consumo_munoz');
    }

}
