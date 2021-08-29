<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EnvioWP extends Model
{
    //
    protected $table = 'envio_whatsapp_prs';
    protected $primaryKey = 'idenvio_whatsapp';

    protected $fillable = [
        'id_instancia', 'estado'
    ];

    public function evidencia () {
        return $this->belongsTo('App\PruebaSerologica', 'idpruebaserologicas');
    }
    public function PruebaSerologica() {
        return $this->belongsTo('App\PruebaSerologica', 'idpruebaserologicas');
    }
    public function consulta(){
        return $this->hasMany('App\ConsultaWhatsAppPrs','idenvio_whatsapp', 'idenvio_whatsapp');
    }
}
