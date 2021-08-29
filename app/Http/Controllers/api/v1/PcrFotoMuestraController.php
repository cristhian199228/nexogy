<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\PcrFotoMuestra;
use App\PcrPruebaMolecular;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class PcrFotoMuestraController extends Controller
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
        $validate = Validator::make($request->all(),[
            'idpcr_prueba_molecular' => 'required|integer|unique:pcr_foto_muestra',
            'file' => 'required',
            'detalle' => 'required'
        ]);

        if($validate->fails()) {
            $respuesta = [
                'code' => 400,
                'status' => 'error',
                'errors' => $validate->errors(),
                'message' => 'Error en campos requeridos',
            ];
        } else {
            $pcr = PcrPruebaMolecular::findOrFail($request->idpcr_prueba_molecular);
            if($pcr->resultado) {
                $image = $request->file('file'); // your base64 encoded
                $imageName = Str::random(10) . '.' . 'jpg';
                $unique_name = md5($imageName . time()) . '.jpg';
                Storage::disk('ftp')->put('/RC/' . $unique_name, file_get_contents($image));

                $foto = new PcrFotoMuestra();
                $foto->idpcr_prueba_molecular = $request->idpcr_prueba_molecular;
                $foto->path = $unique_name;
                $foto->detalle = $request->detalle;
                $foto->save();

                $respuesta = [
                    'code' => 200,
                    'status' => 'success',
                    'data' => $foto
                ];
            } else {
                $respuesta = [
                    'code' => 400,
                    'status' => 'error',
                    'data' => 'Esta prueba molecular no tiene resultado positivo'
                ];
            }
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
        $file = '/RC/' . $path;
        $file = Storage::disk('ftp')->get($file);

        return Image::make($file)->response();
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
        $foto = PcrFotoMuestra::findOrFail($id);
        Storage::disk('ftp')->delete('/RC/'. $foto->path);
        $foto->delete();
        return response($foto);
    }
}
