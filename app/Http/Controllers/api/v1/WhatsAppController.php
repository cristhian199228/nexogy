<?php

namespace App\Http\Controllers\api\v1;

use App\EnvioWP;
use App\EnvioWpAg;
use App\Http\Controllers\Controller;
use App\Jobs\SendMensajeConfirmacionWhatsapp;
use App\Jobs\SendWhatsappPrs;
use App\PacienteIsos;
use App\PruebaAntigena;
use App\PruebaSerologica;
use App\Service\PruebaAntigenaService;
use App\Service\WhatsAppService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhatsAppController extends Controller
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

        $prueba = PruebaSerologica::where('idpruebaserologicas', $request->idpruebaserologicas)
            ->whereNotNull("no_reactivo")
            ->whereHas('FichaPaciente', function (Builder $q) {
                $q->where('enviar_mensaje', 1);
            })
            ->firstOrFail();

        $envio = new EnvioWP();
        $envio->idpruebaserologicas = $prueba->idpruebaserologicas;
        $envio->numero_celular = "51" . $prueba->FichaPaciente->PacienteIsos->celular;
        $envio->id_instancia = 0;
        $envio->estado = 0;
        $envio->save();

        SendWhatsappPrs::dispatch($prueba, $envio);

        return response([
            'message' => 'Mensaje enviado correctamente'
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
    }

    public function enviarWpAntigena(Request $request) {

        $request->validate([
            'id' => 'required'
        ]);
        $ag = PruebaAntigena::where('id', $request->id)
            ->whereHas('ficha', function (Builder $q) {
                $q->where('enviar_mensaje', 1);
            })
            ->whereNotNull('resultado')
            ->firstOrFail();

        $celular = $ag->ficha->PacienteIsos->celular;
        $wp = new WhatsAppService($celular);
        $agService = new PruebaAntigenaService($ag);
        $mensaje = '*Este es un mensaje automático*: Sr(a) ' . $ag->ficha->PacienteIsos->full_name.', el resultado de su prueba antígena del día ' .
            $ag->created_at->format('d-m-Y') . ' es *' . $agService->strResult() . '*. '
            . "\n*Por favor no responder este mensaje*.\n"
            ."- Sus resultados también están disponibles en el siguiente link:\n" .
            CONSTANCIAS_URL;

        $req = $wp->sendMessage($mensaje);

        if(isset($req['request']['sent']) && $req['request']['sent']) {
            $mensaje = new EnvioWpAg();
            $mensaje->id_prueba_antigena = $ag->id;
            $mensaje->numero_celular = "51" . $celular;
            $mensaje->id_instancia = $req['instancia'];
            $mensaje->estado = 1;
            $mensaje->save();

            //$wp->sendConstanciasLink();

            return response([
                'message' => 'mensaje de whatsapp enviado correctamente',
            ]);
        }

        return response([
            'message' => 'Ocurrio un error al enviar el mensaje',
        ], 401);

    }

    public function enviarMensajePrueba(Request $request) {

        $request->validate([
            'celular' => 'required',
            'id_paciente' => 'required'
        ]);
        $paciente = PacienteIsos::findOrFail($request->id_paciente);
        $paciente->celular = $request->celular;
        $paciente->save();

        SendMensajeConfirmacionWhatsapp::dispatch($paciente);

        return response([
            'message' => 'Mensaje de prueba enviado correctamente'
        ]);
    }
}