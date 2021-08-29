<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\PruebaAntigena;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PruebaAntigenaController extends Controller
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
        $request->validate([
            'idficha_paciente' => 'required'
        ]);

        $prueba = PruebaAntigena::where('idficha_paciente', $request->idficha_paciente)
            ->whereDate('created_at', date('Y-m-d'))
            ->latest()
            ->first();

        if ($prueba && !$prueba->finished_at) {
            return response([
                'message' => 'Hay una prueba sin finalizar'
            ], 401);
        }

        $ag = new PruebaAntigena();
        $ag->idficha_paciente = $request->idficha_paciente;
        $ag->id_usuario = Auth::id();
        $ag->save();

        return response([
            'message' => 'Prueba antígena creada correctamente',
        ]);

    }

    /**
     * Inicia la prueba antigena
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function start(int $id) {

        $pa = PruebaAntigena::findOrFail($id);
        $pa->started_at = now();
        $pa->save();

        return response([
            'message' => 'Se inició la prueba antígena correctamente'
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
        $request->validate([
            'resultado' => 'required',
        ]);
        $pa = PruebaAntigena::findOrFail($id);
        $pa->update($request->except('created_at', 'updated_at'));
        $pa->finished_at = now();
        $pa->save();

        return response([
            'message' => 'Se guardó correctamente',
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
        $pa = PruebaAntigena::findOrFail($id);
        $pa->delete();

        return response([
            'message' => 'Se eliminó correctamente'
        ]);
    }
}
