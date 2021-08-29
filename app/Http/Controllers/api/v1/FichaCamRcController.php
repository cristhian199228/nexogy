<?php

namespace App\Http\Controllers\api\v1;

use App\FichaCamRC;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class FichaCamRcController extends Controller
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
            'id_evidencia' => 'unique:rc_evidencia_fc|required|integer',
        ]);

        if($validate->fails()) {
            $respuesta = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Error en campos requeridos',
                'errors' => $validate->errors()
            ];
        } else {
            $ficha = new FichaCamRC();
            $ficha->id_evidencia = $request->id_evidencia;
            $ficha->id_usuario = Auth::id();
            $ficha->save();

            $respuesta = [
                'code' => 200,
                'status' => 'success',
                'message' => 'Ficha CAM creada correctamente',
                'data' => $ficha,
            ];
        }

        return response($respuesta, $respuesta['code']);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $path
     * @return \Illuminate\Http\Response
     */
    public function show(string $path)
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
        $fc = FichaCamRC::where('id', $id)
            ->with('evidencia')
            ->first();

        if ($fc->evidencia->estado) {
            $respuesta = [
                'code' => 400,
                'status' => 'error',
                'message' => 'La evidencia ya fue completada',
            ];
        } else {
            if ($request->firma) {
                $base64Image = trim($request->firma);
                $fc->firma = $base64Image;
            }
            $fc->estado = $request->estado;
            $fc->save();

            $respuesta = [
                'code' => 200,
                'status' => 'success',
                'message' => 'Ficha CAM actualizada correctamente',
                'data' => $fc,
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
        FichaCamRC::findOrFail($id)->delete();

        return response(["message" => "Eliminado correctamente"]);
    }
}
