<?php

namespace App\Http\Controllers\api\v1;

use App\ConsentimientoInformado;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ConsentimientoInformadoController extends Controller
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

        $image = $request->file;
        $imageName = Str::random(10) . '.' . 'jpg';
        $unique_name = md5($imageName . time()) . '.jpg';
        Storage::disk('ftp')->put('/CI/' . $unique_name, file_get_contents($image));

        $ci = new ConsentimientoInformado();
        $ci->idfichapacientes = $request->id_ficha;
        $ci->path = $unique_name;
        $ci->id_usuario = Auth::id();
        $ci->save();

        return response([
            'message' => 'Foto subida correctamente'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  string $path
     * @return \Illuminate\Http\Response
     */
    public function show(string $path)
    {
        //
        $file = '/CI/' . $path;
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
    public function destroy(Request $request, $id)
    {
        //
        $file = '/CI/' . $request->path;
        Storage::disk('ftp')->delete($file);
        ConsentimientoInformado::where('idconsentimientoinformados', $id)->delete();

        return response([
            'message' => "Foto eliminada correctamente"
        ]);
    }
}
