<?php

namespace App\Http\Controllers\api\v1;

use App\EnvioWP;
use App\EnvioWpPcr;
use App\FichaInvestigacion;
use App\FichaInvFoto;
use App\FichaPaciente;
use App\Http\Controllers\Controller;
use App\MensajeWhatsApp;
use App\PcrEnvioMunoz;
use App\PcrPruebaMolecular;
use App\PcrTipo;
use App\Service\PruebaMolecularService;
use App\Service\WhatsAppService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PruebaMolecularController extends Controller
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
            'idficha_paciente' => 'required|unique:pcr_pruebas_moleculares',
        ]);

        $pm = new PcrPruebaMolecular();
        $pm->idficha_paciente = $request->idficha_paciente;
        $pm->hash = $request->idficha_paciente . date('Ymd');
        $pm->id_usuario = Auth::id();
        $pm->save();

        $fichainv = new FichaInvestigacion();
        $fichainv->idpcr_prueba_molecular = $pm->idpcr_pruebas_moleculares;
        $fichainv->id_usuario = Auth::id();
        $fichainv->save();

        return response([
            'message' => 'Prueba molecular creada correctamente'
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
        return PcrPruebaMolecular::find($id);
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
        $request->validate([
            'precio' => 'required|numeric',
            'tipo' => 'required|integer',
            'enviar_mensaje' => 'required|boolean',
        ]);

        $pcr = PcrPruebaMolecular::findOrFail($id);
        $pcr->resultado = $request->resultado;
        $pcr->precio = $request->precio;
        $pcr->tipo = $request->tipo;
        $pcr->detalle = $request->detalle;
        $pcr->save();

        if ($request->enviar_mensaje){

            $celular = $pcr->FichaPaciente->PacienteIsos->celular;
            $fecha = $pcr->created_at->format('d-m-Y');
            $pcrService = new PruebaMolecularService($pcr);
            $resultado_pcr = $pcrService->whatsAppResult();
            $paciente = $pcr->Fichapaciente->PacienteIsos;

            $mensaje_pcr = '*Este es un mensaje automático*: Sr(a) ' . $paciente->full_name.', el resultado de su prueba molecular del día ' .
                $fecha . ' es *' . $resultado_pcr['resultado'] . '*. '
                . $resultado_pcr['comentario'] . "\n*Por favor no responder este mensaje*.\n"
                ."- Sus resultados también están disponibles en el siguiente link:\n" .
                CONSTANCIAS_URL;

            $wp = new WhatsAppService($celular);
            $req = $wp->sendMessage($mensaje_pcr);

            if(isset($req['request']['sent']) && $req['request']['sent']) {
                $envio = new EnvioWpPcr();
                $envio->idpcr_prueba_molecular = $pcr->idpcr_pruebas_moleculares;
                $envio->numero_celular = "51" .$celular;
                $envio->id_instancia = $req['instancia'];
                $envio->estado = 1;
                $envio->save();

                //$wp->sendConstanciasLink();
            }
        }

        return response([
            'message' => 'Se actualizó correctamente'
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
        $pcr = PcrPruebaMolecular::where("idpcr_pruebas_moleculares", $id)
            ->with("PcrEnvioMunoz")
            ->with("FichaInvestigacion.FichaInvFoto")
            ->first();

        if($pcr) {
            if($pcr->PcrEnvioMunoz) {
                //Agregar anulacion de orden muñoz
                PcrEnvioMunoz::find($pcr->PcrEnvioMunoz->idpcr_envio_munoz)->delete();
            }
            if($pcr->FichaInvestigacion) {
                if($pcr->FichaInvestigacion->FichaInvFoto) {
                    if($pcr->FichaInvestigacion->FichaInvFoto->path) {
                        Storage::disk('ftp')->delete('/FI/' . $pcr->FichaInvestigacion->FichaInvFoto->path);
                    }
                    if($pcr->FichaInvestigacion->FichaInvFoto->path2) {
                        Storage::disk('ftp')->delete('/FI2/' . $pcr->FichaInvestigacion->FichaInvFoto->path2);
                    }
                    FichaInvFoto::find($pcr->FichaInvestigacion->FichaInvFoto->idinv_ficha_foto)->delete();
                }
                FichaInvestigacion::find($pcr->FichaInvestigacion->idinv_ficha)->delete();
            }

            $pcr->delete();
            $res = [
                'code' => 200,
                'message' => "Eliminado Correctamente",
            ];

        } else {
            $res = [
                'code' => 400,
                'message' => "No existe la prueba molecular",
            ];
        }

        return response($res, $res['code']);
    }

    /**
     * Finish pcr
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function finish($id)
    {
        $pcr = PcrPruebaMolecular::findOrFail($id);
        $pcr->hora_fin = date('H:i:s ');
        $pcr->save();

        return response([
            "message" => "Prueba molecular finalizada correctamente"
        ]);
    }

    /**
     * SHOW PCR TYPES
     *
     */
    public function showTipos()
    {
        return PcrTipo::all();
    }

    public function enviar(Request $request){
        //dd($request->all());
        PcrPruebaMolecular::whereIn('idpcr_pruebas_moleculares', $request->ids)->update(['id_envio_laboratorio' => $request->id_envio_laboratorio]);
        return ['message' => 'Actualizado correctamente'];
    }
}
