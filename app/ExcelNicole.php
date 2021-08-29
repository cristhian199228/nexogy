<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExcelNicole extends Model
{
    //
    protected $table = "excelnicole";
    protected $primaryKey = "idexcelnicole";

    public function PacienteIsos(){
        return $this->belongsTo('App\PacienteIsos','numero_documento','numero_documento');
    }
}
