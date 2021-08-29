<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ProgramacionDiariaCvImport implements ToCollection, WithStartRow
{
    use Importable;

    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        //
        foreach ($rows as $row) {
            DB::table('programacion_diaria_cv')->insert([
                'fecha' => Date::excelToDateTimeObject($row[1])->format('Y-m-d'),
                'nombres' => $row[2],
                'tipo_documento' => $row[3],
                'numero_documento' => $row[4],
                'registro' => $row[5],
                'empresa' => $row[6],
                'puesto' => $row[7],
                'rol' => $row[8],
                'ruc' => $row[9],
                'turno' => $row[10],
                'prueba' => $row[11],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            /*ProgramacionDiariaCv::create([
                'fecha' => Carbon::parse($row[1])->format('Y-m-d'),
                'nombres' => $row[2],
                'tipo_documento' => $row[3],
                'numero_documento' => $row[4],
                'registro' => $row[5],
                'empresa' => $row[6],
                'puesto' => $row[7],
                'rol' => $row[8],
                'ruc' => $row[9],
                'turno' => $row[10],
                'prueba' => $row[11]
            ]);*/
        }
    }

    public function startRow(): int
    {
        // TODO: Implement startRow() method.
        return 2;
    }
}
