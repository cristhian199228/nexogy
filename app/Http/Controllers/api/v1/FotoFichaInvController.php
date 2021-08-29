<?php

namespace App\Http\Controllers\api\v1;

use App\FichaInvFoto;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class FotoFichaInvController extends Controller
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
            'idinv_ficha' => 'required|unique:inv_ficha_foto',
            'file' => 'required'
        ]);

        $image = $request->file('file'); // your base64 encoded
        $imageName = Str::random(10) . '.' . 'jpg';
        $unique_name = md5($imageName . time()) . '.jpg';
        Storage::disk('ftp')->put('/FI/' . $unique_name, file_get_contents($image));

        $fi = new FichaInvFoto();
        $fi->idinv_ficha = $request->idinv_ficha;
        $fi->path = $unique_name;
        $fi->hash = $request->idinv_ficha . date('Ymd');
        $fi->id_usuario = Auth::id();
        $fi->save();

        return response([
            'message' => 'Foto subida correctamente'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $path
     * @return \Illuminate\Http\Response
     */
    public function show(string $path)
    {
        $file = '/FI/' . $path;
        $file = Storage::disk('ftp')->get($file);

        return Image::make($file)->response();
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $path
     * @return \Illuminate\Http\Response
     */
    public function show2(string $path)
    {
        $file = '/FI2/' . $path;
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

    }

    /**
     * Update path2.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update2(Request $request)
    {
        $validate = Validator::make($request->all(),[
            'file' => 'required',
            'idinv_ficha_foto' => 'required'
        ]);

        if($validate->fails()) {
            $respuesta = [
                'code' => 400,
                'status' => 'error',
                'meessage' => 'Error en campos requeridos',
                'errors' => $validate->errors()
            ];

        } else {
            $image = $request->file('file'); // your base64 encoded
            $imageName = Str::random(10) . '.' . 'jpg';
            $unique_name = md5($imageName . time()) . '.jpg';
            Storage::disk('ftp')->put('/FI2/' . $unique_name, file_get_contents($image));

            $fi = FichaInvFoto::find($request->idinv_ficha_foto);
            $fi->path2 = $unique_name;
            $fi->save();

            $respuesta = [
                'code' => 200,
                'status' => 'success',
                'message' => 'Foto subida correctamente',
                'updated' => $fi
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
    public function destroy(Request $request, $id)
    {
        $validate = Validator::make($request->all(),[
            'path' => 'required',
        ]);

        if($validate->fails()) {
            $respuesta = array(
                'code' => 400,
                'status' => 'error',
                'errors' => $validate->errors()
            );
        } else {
            $file = '/FI/' . $request->path;
            Storage::disk('ftp')->delete($file);

            $fi = FichaInvFoto::find($id);
            if ($fi->path2) {
                $fi->path = null;
                $fi->save();
            } else {
                $fi->delete();
            }

            $respuesta = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'Foto eliminada correctamente',
            );
        }
        return response($respuesta, $respuesta['code']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy2(Request $request, $id)
    {
        $validate = Validator::make($request->all(),[
            'path' => 'required',
        ]);

        if($validate->fails()) {
            $respuesta = array(
                'code' => 400,
                'status' => 'error',
                'errors' => $validate->errors()
            );
        } else {
            $file = '/FI2/' . $request->path;
            Storage::disk('ftp')->delete($file);

            $fi = FichaInvFoto::find($id);
            if ($fi->path) {
                $fi->path2 = null;
                $fi->save();
            } else {
                $fi->delete();
            }

            $respuesta = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'Foto eliminada correctamente',
            );
        }
        return response($respuesta, $respuesta['code']);
    }
}
