<?php

namespace App\Http\Controllers\api\v1;

use App\EvidenciaRC;
use App\Http\Controllers\Controller;
use App\IndicacionesMedicas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IndicacionesMedicasController extends Controller
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
        ]);

        $evidencia = EvidenciaRC::findOrFail($request->id_evidencia);
        $dni = $evidencia->paciente->numero_documento;

        $descansoMedicoMw = DB::connection('mw_asis')
            ->table('paciente')
            ->join('paciente_comprobante', 'paciente_comprobante.idpaciente', '=', 'paciente.idpaciente')
            ->join('descansomedico', 'descansomedico.idcomprobante', '=', 'paciente_comprobante.idcomprobante')
            ->join('comprobante', 'paciente_comprobante.idcomprobante', '=', 'comprobante.idcomprobante')
            ->join('puntoatencion', 'comprobante.idpuntoatencion', '=', 'puntoatencion.idpuntoatencion')
            ->selectRaw("CONVERT(VARCHAR(MAX), DECRYPTBYPASSPHRASE('9utHxlI7{4(XcWc',paciente.nombres)) as nombre,
            CONVERT(VARCHAR(MAX), DECRYPTBYPASSPHRASE('9utHxlI7{4(XcWc',paciente.apellido_paterno)) as a_paterno,
            CONVERT(VARCHAR(MAX), DECRYPTBYPASSPHRASE('9utHxlI7{4(XcWc',paciente.apellido_materno)) as a_materno ,
            CONVERT(VARCHAR(MAX), DECRYPTBYPASSPHRASE('9utHxlI7{4(XcWc',paciente.dni)) as dni, 
            descansomedico.numero_emi, 
            descansomedico.desdedescanso,
            descansomedico.hastadescanso,
            descansomedico.diasdescanso,
            descansomedico.observaciones, 
            descansomedico.recomendaciones,
             descansomedico.descr_espvalorada
            ")
            ->whereRaw("descansomedico.estado = 1 and puntoatencion.idpuntoatencion = 10 and 
            CONVERT(VARCHAR(MAX), DECRYPTBYPASSPHRASE('9utHxlI7{4(XcWc',paciente.dni)) = '$dni'")
            ->orderBy('paciente_comprobante.fecha_auditar', 'DESC')
            ->first();

        if (!$descansoMedicoMw) {
            return response([
                'message' => 'El descanso médico no fué generado en mediweb'
            ], 401);
        }

        $indicaciones = new IndicacionesMedicas();
        $indicaciones->id_evidencia = $request->id_evidencia;
        $indicaciones->fecha_inicio = Carbon::parse($descansoMedicoMw->desdedescanso)->format('Y-m-d');
        $indicaciones->dias_descanso = $descansoMedicoMw->diasdescanso;
        $indicaciones->descr_espvalorada = $descansoMedicoMw->descr_espvalorada;
        $indicaciones->id_usuario = Auth::id();
        $indicaciones->nombre_doctor = $request->usuario['name'];
        $indicaciones->save();

        return response([
            'message' => 'Indicaciones médicas creadas correctamente',
            'data' => $indicaciones
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
        return IndicacionesMedicas::where('id_evidencia', $id)->first();
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
        $data = $request->validate([
            'fecha_inicio' => 'required|date_format:Y-m-d',
            'dias_descanso' => 'required|integer|between:1,30',
            'firma_doctor' => 'required',
            'firma_paciente' => '',
            'descr_espvalorada' => 'required',
        ]);
        
        $evidenciaCerrada = EvidenciaRC::whereHas('indicaciones', function(Builder $q) use ($id) {
                $q->where('id', $id);
            })->where('estado', 1)
            ->first();

        if ($evidenciaCerrada) {
            return response([
                'message' => 'Esta evidencia ya se completó'
            ], 401);
        }

        IndicacionesMedicas::where('id', $id)->update($data);

        return response([
            'message' => 'Actualizado correctamente'
        ]);
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
        IndicacionesMedicas::where('id_evidencia', $id)->delete();

        return response([
            'message' => 'Eliminado correctamente'
        ]);
    }
}
