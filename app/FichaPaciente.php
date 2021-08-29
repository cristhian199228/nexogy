<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FichaPaciente extends Model
{
    //
    protected $primaryKey = 'idficha_paciente';
    protected $table = 'fichapacientes';
    public function PruebaSerologica()
    {
        return $this->hasMany('App\PruebaSerologica', 'idfichapacientes', 'idficha_paciente');

        //return $this->hasMany('App\Pago','idvecino', 'id');
    }

    public function AnexoTres()
    {

        return $this->hasMany('App\AnexoTres', 'idfichapacientes', 'idficha_paciente');
        //return $this->hasMany('App\Pago','idvecino', 'id');
    }
    public function DeclaracionJurada()
    {

        return $this->hasMany('App\DeclaracionJurada', 'idfichapacientes', 'idficha_paciente');
        //return $this->hasMany('App\Pago','idvecino', 'id');
    }
    public function ConsentimientoInformado()
    {

        return $this->hasMany('App\ConsentimientoInformado', 'idfichapacientes', 'idficha_paciente');
        //return $this->hasMany('App\Pago','idvecino', 'id');
    }
    public function DatosClinicos()
    {

        return $this->hasMany('App\DatosClinicos', 'idfichapacientes', 'idficha_paciente');
        //return $this->hasMany('App\Pago','idvecino', 'id');
    }
    public function AntecedentesEp()
    {

        return $this->hasMany('App\AntecedentesEp', 'idfichapacientes', 'idficha_paciente');
        //return $this->hasMany('App\Pago','idvecino', 'id');
    }
    public function Temperatura()
    {

        return $this->hasMany('App\Temperatura', 'idfichapacientes', 'idficha_paciente');
        //return $this->hasMany('App\Pago','idvecino', 'id');
    }
    public function Estacion()
    {
        return $this->belongsTo('App\Estacion', 'id_estacion');
        //return $this->BelongsTo('App\Vecino','idvecino');
    }
    public function Empresa()
    {
        return $this->belongsTo('App\Empresa', 'idempresa');
        //return $this->BelongsTo('App\Vecino','idvecino');
    }
    public function Distrito()
    {
        return $this->belongsTo('App\Distrito', 'id_distrito');
        //return $this->BelongsTo('App\Vecino','idvecino');
    }
    public function PcrPruebaMolecular(){
        return $this->hasOne('App\PcrPruebaMolecular', 'idficha_paciente','idficha_paciente' );
    }
    public function CitasMw() {
        return $this->hasOne('App\CitasMw','idficha_paciente','idficha_paciente');
    }
    public function PacienteIsos() {
        return $this->belongsTo('App\PacienteIsos','id_paciente','idpacientes');
    }

    public function pruebaAntigena() {
        return $this->hasMany('App\PruebaAntigena', 'idficha_paciente', 'idficha_paciente');
    }
    
}
