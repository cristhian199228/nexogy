<?php

namespace App\Http\Controllers\api\v1;

use App\FichaEpidemiologicaRC;
use App\Http\Controllers\Controller;
use App\PacienteIsos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FichaEpRcController extends Controller
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
            'id_evidencia' => 'unique:rc_evidencia_fe|required|integer',
        ]);

        if($validate->fails()) {
            $respuesta = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Error en campos requeridos',
                'errors' => $validate->errors()
            ];
        } else {
            $ficha = new FichaEpidemiologicaRC();
            $ficha->id_evidencia = $request->id_evidencia;
            $ficha->id_usuario = Auth::id();
            $ficha->save();

            $respuesta = [
                'code' => 200,
                'status' => 'success',
                'message' => 'Ficha epidemiológica creada correctamente',
                'data' => $ficha,
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
        $ficha = FichaEpidemiologicaRC::where('id', $id)
        ->with('evidencia')
        ->first();

        if ($ficha->evidencia->estado) {
            $respuesta = [
                'code' => 400,
                'status' => 'error',
                'message' => 'La evidencia ya fue completada',
            ];

        } else {
            $paciente = PacienteIsos::find($request->id_paciente);
            if ($request->direccion && ($request->direccion !== $paciente->direccion)) {
                $paciente->direccion = $request->direccion;
            }
            if ($request->celular && ($request->celular !== $paciente->celular)) {
                $paciente->celular = $request->celular;
            }
            if ($request->nro_registro && ($request->nro_registro !== $paciente->nro_registro)) {
                $paciente->nro_registro = $request->nro_registro;
            }
            $paciente->save();

            $ficha->nombre_supervisor = $request->nombre_supervisor;
            $ficha->celular_supervisor	 = $request->celular_supervisor;
            $ficha->p1_fecha_inicio = $request->p1_fecha_inicio;
            $ficha->p1_fecha_fin = $request->p1_fecha_fin;
            $ficha->prueba_positiva = $request->prueba_positiva;
            $ficha->prueba_cv = $request->prueba_cv;
            $ficha->prueba_otro = $request->prueba_otro;
            $ficha->prueba_otro_tipo = $request->prueba_otro_tipo;
            $ficha->prueba_otro_fecha = $request->prueba_otro_fecha;
            $ficha->prueba_otro_resultado = $request->prueba_otro_resultado;
            $ficha->prueba_otro_lugar = $request->prueba_otro_lugar;
            $ficha->observaciones = $request->observaciones;
            if ($request->firma) {
                $base64Image = trim($request->firma);
                $ficha->firma = $base64Image;
            }
            $ficha->save();
            $respuesta = [
                'code' => 200,
                'status' => 'success',
                'message' => 'Ficha epidemiologica actualizada correctamente',
                'data' => $ficha,
            ];
        }

        return response($respuesta, $respuesta['code']);
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
        $ficha = FichaEpidemiologicaRC::findOrFail($id);
        $ficha->delete();

        return response(["message" => "Eliminado correctamente"]);

    }
}
