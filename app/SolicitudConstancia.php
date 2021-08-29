<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SolicitudConstancia extends Model
{
    //

    protected $table = 'constancias_solicitudes';

    protected $fillable = [
	    'dni',
	    'nombres',
	    'apellidos',
	    'email',
	    'celular',
	    'fechaNacimiento',
        'mensaje'
    ];
    
}
