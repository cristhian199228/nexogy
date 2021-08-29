<?php

namespace App\Http\Controllers\api\v1;

use App\AnexoTres;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class AnexoTresController extends Controller
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
            'file' => 'required',
            'id_ficha' => 'required'
        ]);

        $image = $request->file('file');
        $imageName = Str::random(10) . '.' . 'jpg';
        $unique_name = md5($imageName . time()) . '.jpg';
        Storage::disk('ftp')->put('/ATRES/' . $unique_name, file_get_contents($image));

        $a3 = new AnexoTres();
        $a3->idfichapacientes = $request->id_ficha;
        $a3->path = $unique_name;
        $a3->id_usuario = Auth::id();
        $a3->save();

        return response([
            'message' => 'foto subida correctamente'
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
        $file = '/ATRES/' . $path;
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
        $file = '/ATRES/' . $request->path;
        Storage::disk('ftp')->delete($file);
        AnexoTres::where('idanexotres', $id)->delete();

        return response([
            'message' => "Foto eliminada correctamente"
        ]);
    }
}
