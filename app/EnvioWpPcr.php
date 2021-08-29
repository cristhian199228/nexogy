<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EnvioWpPcr extends Model
{
//
    protected $table = "envio_whatsapp_pcr";
    protected $casts = [
        'estado' => 'boolean'
    ];

    protected $fillable = [
        'id_instancia', 'estado'
    ];

    public function PcrPruebaMolecular(){
        return $this->belongsTo('App\PcrPruebaMolecular', 'idpcr_prueba_molecular', 'idpcr_pruebas_moleculares');
    }

    public function consulta(){
        return $this->hasMany('App\ConsultaWhatsAppPcr', 'id_envio_whatsapp_pcr');
    }
}