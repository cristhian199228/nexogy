<?php

namespace App\Http\Controllers\api\v1;

use App\AntecedentesEp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AntecedentesEpidemiologicosController extends Controller
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
            'dias_viaje' => 'required|boolean',
            'contacto_cercano' => 'required|boolean',
            'conv_covid' => 'required|boolean',
            'paises_visitados' => '',
            'debilite_sistema' => '',
            'medio_transporte' => '',
            'fecha_llegada' => '',
            'fecha_ultimo_contacto' => '',
            'usuario' => '',
            /*'embarazo' => 'required',
            'enfermedad_cardiovascular' => 'required',
            'diabetes' => 'required',
            'enfermedad_hepatica' => 'required',
            'enfermedad_cronica' => 'required',
            'pos_parto' => 'required',
            'inmunodeficiencia' => 'required',
            'enfermedad_renal' => 'required',
            'dano_hepatico' => 'required',
            'enfermedad_pulmonar' => 'required',
            'cancer' => 'required',
            'condicion_otro' => 'required',*/
        ]);

        $data['id_usuario'] = Auth::id();

        AntecedentesEp::create($data);

        return response([
            'message' => 'Antecedentes guardados correctamente'
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
        $data = $request->validate([
            'idfichapacientes' => 'required',
            'dias_viaje' => 'required|boolean',
            'contacto_cercano' => 'required|boolean',
            'conv_covid' => 'required|boolean',
            'paises_visitados' => '',
            'debilite_sistema' => '',
            'medio_transporte' => '',
            'fecha_llegada' => '',
            'fecha_ultimo_contacto' => '',
            'usuario' => '',
            /*'embarazo' => 'required',
            'enfermedad_cardiovascular' => 'required',
            'diabetes' => 'required',
            'enfermedad_hepatica' => 'required',
            'enfermedad_cronica' => 'required',
            'pos_parto' => 'required',
            'inmunodeficiencia' => 'required',
            'enfermedad_renal' => 'required',
            'dano_hepatico' => 'required',
            'enfermedad_pulmonar' => 'required',
            'cancer' => 'required',
            'condicion_otro' => 'required',*/
        ]);

        AntecedentesEp::where('idaepidemologicos', $id)->update($data);

        return response([
            'message' => 'Antecedentes guardados correctamente'
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
        AntecedentesEp::destroy($id);

        return response([
            'message' => 'Eliminado correctamente'
        ]);
    }
}
