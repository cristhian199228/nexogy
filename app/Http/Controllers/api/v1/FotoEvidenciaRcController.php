<?php

namespace App\Http\Controllers\api\v1;

use App\FotoEvidenciaRC;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class FotoEvidenciaRcController extends Controller
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
            'id_evidencia' => 'required',
            'file' => 'required'
        ]);

        $id_evidencia = $request->id_evidencia;
        $file = $request->file('file');
        $fileName = Str::random(10) . '.' . 'jpg';
        $unique_name = md5($fileName . time()) . '.jpg';
        if ($file->extension() === 'pdf') {
            $fileName = Str::random(10) . '.' . 'pdf';
            $unique_name = md5($fileName . time()) . '.pdf';
        }

        //guardar foto en storage local
        $public_dir = 'storage/fotos_temporal/' . $id_evidencia . '/';
        $storage_dir = 'public/fotos_temporal/' . $id_evidencia . '/';
        if (!File::exists($public_dir)) {
            File::makeDirectory($public_dir, $mode = 0777, true, true);
        }
        Storage::put($storage_dir . $unique_name, file_get_contents($file));
        //comprimir foto
        $optimizerChain = OptimizerChainFactory::create();
        $optimizerChain->optimize($public_dir . $unique_name);
        //subir foto a ftp
        $contents = Storage::get($storage_dir . $unique_name);
        Storage::disk('ftp')->put('/RC/' . $unique_name, $contents);
        $foto = new FotoEvidenciaRC();
        $foto->id_evidencia = $id_evidencia;
        $foto->path = $unique_name;
        $foto->save();

        File::deleteDirectory($public_dir, false);

        return response([
            "message" => 'Foto subida correctamente'
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
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, int $id)
    {
        //
        $file = '/RC/' . $request->path;
        Storage::disk('ftp')->delete($file);
        FotoEvidenciaRC::findOrFail($id)->delete();

        return response([
            'message' => "Foto eliminada correctamente"
        ]);
    }
}
