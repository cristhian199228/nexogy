<?php

namespace App\Http\Controllers\api\v1;

use App\FichaEpidemiologicaContactoRC;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FichaContactoRcController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $validate = Validator::make($request->all(), [
            'id_fe' => 'required|integer',
            'nombres' => 'required',
        ]);
        if($validate->fails()) {
            $respuesta = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Error en campos requeridos',
                'errors' => $validate->errors()
            ];
        } else {
            $contacto = new FichaEpidemiologicaContactoRC();
            $contacto->id_evidencia_fe = $request->id_fe;
            $contacto->nombres = Str::title($request->nombres);
            $contacto->celular = $request->celular;
            $contacto->cargo = $request->cargo;
            $contacto->detalle = $request->detalle;
            $contacto->save();

            $respuesta = [
                'code' => 200,
                'status' => 'success',
                'message' => 'Contacto directo creado correctamente',
                'data' => $contacto
            ];
        }
        return response($respuesta, $respuesta['code']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $contactos = FichaEpidemiologicaContactoRC::where('id_evidencia_fe', $id)->latest()->get();
        $contador = $contactos->count();
        foreach ($contactos as $c) {
            $c->contador = $contador;
            $contador--;
        }

        return response($contactos);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
