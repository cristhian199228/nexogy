<?php

namespace App\Http\Controllers\api\v1;

use App\EvidenciaRC;
use App\FotoEvidenciaRC;
use App\Http\Controllers\Controller;
use App\PacienteIsos;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZanySoft\Zip\Zip;
use ZipArchive;
use Illuminate\Support\Facades\File;
use Barryvdh\DomPDF\PDF;
use Imagick;

class ResponceCenterController extends Controller
{
    //
    public function validarPaciente(Request $request) {

        $nro_documento = trim($request->numero_documento);
        $fecha_nac = Carbon::parse($request->fecha_nacimiento)->format('Y-m-d');

        $paciente = PacienteIsos::where('numero_documento', $nro_documento)
            ->where('fecha_nacimiento', $fecha_nac)
            ->with('Empresa:idempresa,descripcion')
            ->first();

        if ($paciente) {
            $nom_completo = $paciente->nombres . " " .$paciente->apellido_paterno . " " .$paciente->apellido_materno;
            $paciente->nom_completo = $nom_completo;
            $respuesta = [
                'code' => 200,
                'status' => 'success',
                'message' => 'Paciente validado correctamente',
                'data' => $paciente
            ];
        } else {
            $respuesta = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Datos inválidos'
            ];
        }

        return response()->json($respuesta, $respuesta['code']);
    }

    public function generarIndicacionesMedicas(Request $request) {

        $evidencia = EvidenciaRC::where('id', $request->id_evidencia)->has('indicaciones')->firstOrFail();
        $pdf = $this->generarPdfIndicacionesMedicas($evidencia);
        return $pdf->stream('Indicaciones_Medicas_'. $evidencia->id .'.pdf');
    }

    public function generarFichaEpidemiologica(Request $request)
    {
        $evidencia = EvidenciaRC::where('id', $request->id_evidencia)->has('fichaEp')->firstOrFail();
        $pdf = $this->generarPdfFichaEp($evidencia);
        return $pdf->stream('Ficha_Epidemiologica_'. $evidencia->id .'.pdf');
    }

    public function generarFichaCam(Request $request)
    {
        $evidencia = EvidenciaRC::where('id', $request->id_evidencia)->has('fichaCam')->firstOrFail();
        $pdf = $this->generarPdfFichaCam($evidencia);
        return $pdf->stream('Ficha_CAM_'.$evidencia->id .'.pdf');
    }

    public function downloadImages(Request $request) {

        $id = $request->id_evidencia;

        $evidencia = EvidenciaRC::findOrFail($id);

        $fileName = 'zipFile'. $id.'.zip';
        $public_dir = 'storage/fotos/' . $id . '/';
        $storage_dir = 'public/fotos/' . $id . '/';
        $public_dir_zip = 'storage/zip/';

        if (!File::exists($public_dir_zip)) {
            File::makeDirectory($public_dir_zip, $mode = 0777, true, true);
        }
        if (!File::exists($public_dir)) {
            File::makeDirectory($public_dir, $mode = 0777, true, true);
        }

        $zip = new ZipArchive();
        $res = $zip->open(public_path( $public_dir_zip.$fileName), ZipArchive::CREATE);

        if ($res === TRUE) {
            if ($evidencia->fichaCam) {
                $namePdfCam = 'Ficha_CAM_'.$id . '.pdf';
                $pdf_cam = $this->generarPdfFichaCam($evidencia);
                $pdf_cam->save($public_dir.$namePdfCam);
            }
            if($evidencia->fichaEp) {
                $namePdfEp = 'Ficha_Epidemiologica_'.$id . '.pdf';
                $pdf_ep = $this->generarPdfFichaEp($evidencia);
                $pdf_ep->save($public_dir.$namePdfEp);
            }
            if($evidencia->indicaciones) {
                $namePdfIm = 'Indicaciones_Medicas_'. $id . '.pdf';
                $pdf_im = $this->generarPdfIndicacionesMedicas($evidencia);
                $pdf_im->save($public_dir.$namePdfIm);
            }

            foreach ($evidencia->fotos as $foto) {
                $file = Storage::disk('ftp')->get('/RC/'. $foto->path);
                Storage::put( $storage_dir. $foto->path, $file);
            }

            $files = File::files(public_path($public_dir));
            foreach ($files as $key => $value){
                $relativeName = basename($value);
                $zip->addFile($value, $relativeName);
            }
            $zip->close();
            File::deleteDirectory($public_dir, false);
        }
        return response()->download(public_path( $public_dir_zip.$fileName));
    }

    public function showPdfEvidencia($path) {
        $path = "/RC/$path";
        try {
            $file = Storage::disk('ftp')->get($path);
            return response($file)->withHeaders([
                'Content-Type' => 'application/pdf'
            ]);
        } catch (FileNotFoundException $e) {
            abort(404);
        }
    }

    public function generarPdfIndicacionesMedicas(EvidenciaRC $evidencia) {

        $data = [
            'descr_espvalorada' => $evidencia->indicaciones->descr_espvalorada,
            'firma_doctor' =>  $evidencia->indicaciones->firma_doctor,
            'firma_paciente' =>  $evidencia->indicaciones->firma_paciente,
            'nombre_doctor' => $evidencia->indicaciones->nombre_doctor,
            'nombre_paciente' => Str::title($evidencia->paciente->full_name),
        ];

        return \PDF::loadView('pdf.ficha_indicaciones', compact('data'));
    }

    public function generarPdfFichaEp(EvidenciaRC $evidencia) {

        $ficha = [
            'nom_completo' => Str::title($evidencia->paciente->full_name),
            'dni' => $evidencia->paciente->numero_documento,
            'direccion' => Str::title($evidencia->paciente->direccion),
            'empresa' => $evidencia->paciente->Empresa->descripcion,
            'celular' => $evidencia->paciente->celular,
            'registro' => $evidencia->paciente->nro_registro,
            'supervisor' => Str::title($evidencia->fichaEp->nombre_supervisor),
            'cel_supervisor' => $evidencia->fichaEp->celular_supervisor,
            'p_inicio' => $evidencia->fichaEp->p1_fecha_inicio ? Carbon::parse($evidencia->fichaEp->p1_fecha_inicio)->format('d/m/Y') : '',
            'p_final' => $evidencia->fichaEp->p1_fecha_fin ? Carbon::parse($evidencia->fichaEp->p1_fecha_fin)->format('d/m/Y') : '',
            'prueba_positiva' => $evidencia->fichaEp->prueba_positiva,
            'prueba_cv' => $evidencia->fichaEp->prueba_cv,
            'prueba_otro' => $evidencia->fichaEp->prueba_otro,
            'prueba_otro_tipo' => $evidencia->fichaEp->prueba_otro_tipo,
            'prueba_otro_fecha' => $evidencia->fichaEp->prueba_otro_fecha ? Carbon::parse($evidencia->fichaEp->prueba_otro_fecha)->format('d/m/Y') : '',
            'prueba_otro_resultado' => $evidencia->fichaEp->prueba_otro_resultado,
            'prueba_otro_adjunto' => $evidencia->fichaEp->prueba_otro_adjunto,
            'prueba_otro_lugar' => $evidencia->fichaEp->prueba_otro_lugar,
            'observaciones' => $evidencia->fichaEp->observaciones,
            'firma' => $evidencia->fichaEp->firma,
            'contactos' => $evidencia->fichaEp->contactos,
            'fecha' => Carbon::parse($evidencia->fichaEp->updated_at)->locale('es')->isoFormat('LL'),
        ];

        return \PDF::loadView('pdf.ficha_epidemiologica', compact('ficha'));
    }

    public function generarPdfFichaCam(EvidenciaRC $evidencia) {

        $ficha = [
            'nom_completo' => Str::upper($evidencia->paciente->full_name),
            'dni' => $evidencia->paciente->numero_documento,
            'empresa' => $evidencia->paciente->Empresa->descripcion,
            'registro' => $evidencia->paciente->nro_registro,
            'firma' => $evidencia->fichaCam->firma,
            'acepta' => $evidencia->fichaCam->estado,
            'fecha' => Carbon::parse($evidencia->fichaCam->updated_at)->locale('es')->isoFormat('LL'),
        ];

        return \PDF::loadView('pdf.ficha_cam', ['ficha' => $ficha]);
    }
}
