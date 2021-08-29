<?php

namespace App\Imports;

use App\Recuperacion;
use Maatwebsite\Excel\Concerns\ToModel;

class RecuperacionImport implements ToModel
{
   /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Recuperacion([
            'nombre'     => $row[1],
            'dni'    => $row[2], 
            'transactionid' => $row[4],
            'idpcrpruebamolecular' => $row[3],
            
            'fecha' => $row[0],
            'estado' => 1,
           
        ]);
    }
}
