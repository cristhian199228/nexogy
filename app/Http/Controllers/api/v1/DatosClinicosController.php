<?php

namespace App\Http\Controllers\api\v1;

use App\CitasMw;
use App\DatosClinicos;
use App\FichaPaciente;
use App\Http\Controllers\Controller;
use App\Service\MediwebService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DatosClinicosController extends Controller
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
        $data = $request->validate([
            'idfichapacientes' => 'required',
            'tos' => 'required|boolean',
            'dolor_garganta' => 'required|boolean',
            'dificultad_respiratoria' => 'required|boolean',
            'fiebre' => 'required|boolean',
            'malestar_general' => 'required|boolean',
            'diarrea' => 'required|boolean',
            'anosmia_ausegia' => 'required|boolean',
            'otros' => '',
            'toma_medicamento' => '',
            'nauseas_vomitos' => 'required|boolean',
            'congestion_nasal' => 'required|boolean',
            'cefalea' => 'required|boolean',
            'irritabilidad_confusion' => 'required|boolean',
            'falta_aliento' => 'required|boolean',
            'usuario' => '',
            'fecha_inicio_sintomas' => 'required',
            /*'dolor_muscular' => 'required',
            'dolor_abdominal' => 'required',
            'dolor_articulaciones' => 'required',
            'dolor_pecho' => 'required',*/
            'post_vacunado' => 'required|boolean',
        ]);

        $data['id_usuario'] = Auth::id();

        $dc = DatosClinicos::create($data);

        if ($data['post_vacunado']) {
            $ficha = FichaPaciente::findOrFail($data['idfichapacientes']);
            $service = new MediwebService($ficha);
            $result = $service->sendRequest();

            if($result) {
                $cita = new CitasMw();
                $cita->idficha_paciente = $ficha->idficha_paciente;
                $cita->tipo = 3;
                $cita->cadena_envio = json_encode($service->getParams());
                $cita->respuesta_ws = json_encode($result);
                $cita->usuario = $request->usuario;
                $cita->save();
            }
        }

        return response([
            'message' => 'Datos clinicos creados correctamente',
            'data' => $dc
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
        $data = $request->validate([
            'idfichapacientes' => 'required',
            'tos' => 'required|boolean',
            'dolor_garganta' => 'required|boolean',
            'dificultad_respiratoria' => 'required|boolean',
            'fiebre' => 'required|boolean',
            'malestar_general' => 'required|boolean',
            'diarrea' => 'required|boolean',
            'anosmia_ausegia' => 'required|boolean',
            'otros' => '',
            'toma_medicamento' => '',
            'nauseas_vomitos' => 'required|boolean',
            'congestion_nasal' => 'required|boolean',
            'cefalea' => 'required|boolean',
            'irritabilidad_confusion' => 'required|boolean',
            'falta_aliento' => 'required|boolean',
            'usuario' => '',
            'fecha_inicio_sintomas' => 'required',
            /*'dolor_muscular' => 'required',
            'dolor_abdominal' => 'required',
            'dolor_articulaciones' => 'required',
            'dolor_pecho' => 'required',*/
            'post_vacunado' => 'required|boolean',
        ]);

        DatosClinicos::where('iddatoclinicos', $id)->update($data);

        return response([
            'message' => 'Datos clinicos guardados correctamente'
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
        $dc = DatosClinicos::find($id);
        $dc->delete();

        return response(['deleted' => $dc]);
    }
}
