<?php

namespace App\Http\Controllers\api\v1;

use App\DepartamentoUbigeo;
use App\DistritoUbigeo;
use App\Http\Controllers\Controller;
use App\ProvinciaUbigeo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UbigeoController extends Controller
{
    //
    public function searchDepartamento(Request $request) {

        $departamentos = DepartamentoUbigeo::where(DB::raw('name'), 'like', '%' . $request->buscar . '%')
            ->limit(10)
            ->get();

        return ["departamentos" => $departamentos];
    }

    public function searchProvincia(Request $request) {

        $provincias = ProvinciaUbigeo::where(DB::raw('name'), 'like', '%' . $request->buscar . '%')
            ->limit(10)
            ->get();

        return ["provincias" => $provincias];
    }

    public function searchDistrito(Request $request) {

        $distritos = DistritoUbigeo::where(DB::raw('name'), 'like', '%' . $request->buscar . '%')
            ->limit(10)
            ->get();

        return ["distritos" => $distritos];
    }
}
