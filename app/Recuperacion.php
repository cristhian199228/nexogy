<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Recuperacion extends Model
{
    //
    protected $table = 'recuperacion';
    protected $fillable = ['nombre','dni','transactionid','idpcrpruebamolecular','fecha','estado'
    ];
}
