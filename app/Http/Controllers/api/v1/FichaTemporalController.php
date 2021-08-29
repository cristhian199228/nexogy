<?php

namespace App\Http\Controllers\api\v1;

use App\FichaTemporal;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFichaTemporalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Psy\Util\Str;

class FichaTemporalController extends Controller
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
            'id_paciente' => 'required',
        ]);
        $fichaCreada = FichaTemporal::where('id_paciente', $request->id_paciente)
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->first();

            //dd($fichaCreada);

        if ($fichaCreada) {
            return response([
                'message' => 'Solo se permite la creación de un cuestionario por día'
            ], 401);
        }

        $fichaTemporal = new FichaTemporal();
        $fichaTemporal->id_paciente = $request->id_paciente;
        $fichaTemporal->hash = $request->id_paciente . now()->format('Ymd');
        $fichaTemporal->save();

        return response([
            'message' => 'Cuestionario creado correctamente',
            'data' => $fichaTemporal
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
        return FichaTemporal::findOrFail($id);
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
        FichaTemporal::where('id', $id)->update($request->except('created_at', 'updated_at'));
        return response([
            'message' => 'Guardado correctamente'
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
        FichaTemporal::destroy($id);

        return response([
            'message' => 'Eliminado correctamente'
        ]);
    }

    public function fichas(Request $request) {

        return FichaTemporal::where('id_paciente', $request->id_paciente)
            ->latest()
            ->get();
    }
}
