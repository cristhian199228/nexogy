<?php

namespace App\Http\Controllers\api\v1;

use App\DeclaracionJurada;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class DeclaracionJuradaController extends Controller
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
            'file' => 'required',
            'id_ficha' => 'required'
        ]);
        $image = $request->file('file');
        $imageName = Str::random(10) . '.' . 'jpg';
        $unique_name = md5($imageName . time()) . '.jpg';
        Storage::disk('ftp')->put('/DJ/' . $unique_name, file_get_contents($image));

        $dj = new DeclaracionJurada();
        $dj->idfichapacientes = $request->id_ficha;
        $dj->path = $unique_name;
        $dj->id_usuario = Auth::id();
        $dj->save();

        return response([
            'message' => 'Foto subida correctamente'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(string $path)
    {
        //
        $file = '/DJ/' . $path;
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
    public function destroy(Request $request,$id)
    {
        //
        $file = '/DJ/' . $request->path;
        Storage::disk('ftp')->delete($file);
        DeclaracionJurada::where('iddeclaracionesjuradas', $id)->delete();

        return response([
            'message' => "Foto eliminada correctamente"
        ]);
    }
}
