<?php

namespace App\Http\Controllers\api\v1;

use App\Firma;
use App\Http\Controllers\Controller;
use App\PacienteIsos;
use Illuminate\Http\Request;

class FirmaController extends Controller
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
        $data = $request->validate([
            'numero_documento' => 'required',
            'firma' => 'required'
        ]);

        PacienteIsos::where('numero_documento', $data['numero_documento'])->firstOr(function () {
            return response([
                'message' => 'El usuario no se encuentra registrado'
            ], 401);
        });

        return response([
            'message' => 'Firma guardada correctamente',
            'data' => Firma::create($data)
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
        return Firma::firstWhere('numero_documento', $id);
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
        Firma::where('id', $id)->update($request->except('created_at', 'updated_at'));

        return response([
            'message' => 'Firma guardada correctamente'
        ]);
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
        Firma::destroy($id);

        return response([
            'message' => 'Firma eliminada correctamente'
        ]);

    }
}
