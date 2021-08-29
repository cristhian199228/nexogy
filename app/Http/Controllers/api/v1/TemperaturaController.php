<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Temperatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TemperaturaController extends Controller
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
        $request->validate([
            'idficha_paciente' => 'required',
            'temperatura' => 'required'
        ]);

        $temperatura = new Temperatura();
        $temperatura->idfichapacientes = $request->idficha_paciente;
        $temperatura->valortemp = $request->temperatura;
        $temperatura->id_usuario = Auth::id();
        $temperatura->save();

        return response([
            'message' => 'Temperatura guardada correctamente'
        ]);

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
        $temperatura = Temperatura::find($id);
        $temperatura->valortemp = $request->temperatura;
        $temperatura->save();

        return response(['updated' => $temperatura]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $temperatura = Temperatura::find($id);
        $temperatura->delete();

        return response(['deleted' => $temperatura]);
    }
}
