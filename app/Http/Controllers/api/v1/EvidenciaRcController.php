<?php

namespace App\Http\Controllers\api\v1;

use App\EvidenciaRC;
use App\Http\Controllers\Controller;
use App\Jobs\SendMailEvidenciasJob;
use App\Jobs\SendWhatsappEvidenciasJob;
use App\PacienteIsos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EvidenciaRcController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
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
        $validated = $request->validate([
            'id_paciente' => 'required',
            'id_empresa' => 'required',
            'celular' => 'required',
            'id_estacion' => 'required'
        ]);

        $ev = EvidenciaRC::where('id_paciente', $validated['id_paciente'])
            ->where('estado', 0)
            ->latest()
            ->first();

        if ($ev) {
            return response([
                'message' => 'Este paciente tiene una evidencia creada sin completar',
                'data' => $ev
            ], 401);
        }

        $paciente = PacienteIsos::find($validated['id_paciente']);
        $paciente->celular = $validated['celular'];
        $paciente->correo = $request->correo;
        $paciente->idempresa = $validated['id_empresa'];
        $paciente->save();

        $evidencia = new EvidenciaRC();
        $evidencia->id_paciente = $validated['id_paciente'];
        $evidencia->id_usuario = Auth::id();
        $evidencia->id_estacion = $validated['id_estacion'];
        $evidencia->usuario = $request->usuario;
        $evidencia->save();

        SendWhatsappEvidenciasJob::dispatch($evidencia);

        if ($request->has('correo')) {
            SendMailEvidenciasJob::dispatch($paciente);
        }

        return response([
            'message' => 'Evidencia creada correctamente',
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
        $evidencias = EvidenciaRC::where('id_paciente', $id)
            ->with('paciente.Empresa', 'fotos','fichaEp.contactos','fichaCam')
            ->withCount('indicaciones')
            ->latest()
            ->get();

        $contador = $evidencias->count();

        foreach ($evidencias as $ev) {
            $ev->contador = $contador;
            $paciente = $ev->paciente;
            $nom_completo = $paciente->nombres . " " .$paciente->apellido_paterno . " " .$paciente->apellido_materno;
            $paciente->nom_completo = Str::upper($nom_completo);
            $ev->fecha = $ev->created_at->format('d/m/Y');
            $contador--;
        }

        return $evidencias;
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
        $evidencia = EvidenciaRC::findOrFail($id);
        $evidencia->estado = 1;
        $evidencia->finished_at = date('Y-m-d H:i:s');
        $evidencia->save();

        $respuesta = [
            'code' => 200,
            'status' => 'success',
            'message' => 'Evidencia completada correctamente',
            'data' => $evidencia,
        ];

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
        $evidencia = EvidenciaRC::findOrFail($id);

        if (count($evidencia->fotos) > 0) {
            foreach ($evidencia->fotos as $foto) {
                $file = '/RC/' . $foto->path;
                Storage::disk('ftp')->delete($file);
            }
        }
        $evidencia->delete();

        return response([
            'message' => 'Evidencia eliminada correctamente'
        ]);
    }

    /**
     * Habilita la subida de fotos de una evidencia
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function habilitarFotos($id)
    {
        //
        $evidencia = EvidenciaRC::findOrFail($id);
        $evidencia->puede_subir_fotos = 1;
        $evidencia->save();

        $respuesta = [
            'code' => 200,
            'status' => 'success',
            'message' => 'Actualizado correctamente',
            'data' => $evidencia,
        ];

        return response($respuesta, $respuesta['code']);
    }
}
