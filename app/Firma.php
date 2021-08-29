<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Firma extends Model
{
    //
    protected $table = 'firmas';

    protected $fillable = [
        'numero_documento',
        'firma'
    ];
}
