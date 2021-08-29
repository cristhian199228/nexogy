<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Paciente;
use App\PacienteIsos;
use App\PcrEnvioMunoz;
use App\PcrPruebaMolecular;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use SoapClient;

class OrdernMunozController extends Controller
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
            'id_paciente' => 'required',
            'idpcr_prueba_molecular' => 'required|unique:pcr_envio_munoz',
        ]);
        $idpcr =$request->idpcr_prueba_molecular;

        $paciente = PacienteIsos::findOrFail($request->id_paciente);

        $arr_pruebas[] = array(
            'Test' => 'PRUEBA MOLECULAR SARS-COV2 (U)',
            'TestCode' => 'PML01'
        );
        $params['newOrder'] = array(
            'Code' => $idpcr, //codigo prueba molecular
            'LastName' => $paciente->apellido_paterno. " " . $paciente->apellido_materno,
            'FirstName' => $paciente->nombres,
            'DNI' =>  $paciente->numero_documento,
            'BirthdayDate' => $paciente->fecha_nacimiento,
            'Estado' => true,
            'FechaProcesado' => date('Y-m-d'),
            'OrderDay' => date('d'),
            'OrderMonth' => date('m'),
            'OrderYear' => date('Y'),
            'User' => 'pcr_isos_user',
            'Source' => '254',
            'Tests' => $arr_pruebas
        );
        $client = new SoapClient('http://207.246.113.125/lmsermedi/webservices/ConnectorAppLab.svc?singleWsdl');
        $result = $client->SetNewOrder($params);

        if($result && $result->SetNewOrderResult) {
            $munoz = new PcrEnvioMunoz();
            $munoz->idpcr_prueba_molecular = $idpcr;
            $munoz->transaction_id = substr($result->SetNewOrderResult, 15);
            $munoz->hash = $idpcr.date('Ymd');
            $munoz->trama_ws = json_encode($params);
            $munoz->respuesta_ws = json_encode($result);
            $munoz->id_usuario = Auth::id();
            $munoz->save();

            return response([
                'message' => 'Orden creada correctamente'
            ]);
        }

        return response([
            'message' => 'Error al enviar a web service'
        ], 401);
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
}
