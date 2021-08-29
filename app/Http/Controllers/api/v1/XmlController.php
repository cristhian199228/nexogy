<?php

namespace App\Http\Controllers\api\v1;

use App\FichaPaciente;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class XmlController extends Controller
{
    //

    public function generar(Request $request) {

        $buscar = $request->buscar;
        $fecha_inicio = $request->fecha_inicio;
        $fecha_fin = $request->fecha_fin;

        $ficha = FichaPaciente::whereBetween(DB::raw('DATE(created_at)'), [$fecha_inicio, $fecha_fin])
            ->whereHas("PacienteIsos", function ($q) use ($buscar){
                $q->search($buscar);
            })
            ->with('PruebaSerologica', 'DatosClinicos', 'AntecedentesEp', 'PcrPruebaMolecular','PacienteIsos')
            ->latest()
            ->get();


    }

}
