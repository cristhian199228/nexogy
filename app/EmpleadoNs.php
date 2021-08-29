<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmpleadoNs extends Model
{
    //
    protected $connection = 'sqlsrv';
    protected $table = 'PLA_Trabajadores';
}
