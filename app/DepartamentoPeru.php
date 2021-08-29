<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DepartamentoPeru extends Model
{
    protected $table = 'graldepartamentos';
    protected $primaryKey = 'iddepartamento';

    public function Provincia()
    {
        return $this->hasMany('App\Provincia', 'ideprovincia', 'idprovincia');
        //return $this->hasMany('App\Pago','idvecino', 'id');
    }
    
   
}
