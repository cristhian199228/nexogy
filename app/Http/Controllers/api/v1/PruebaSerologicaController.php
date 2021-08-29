<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\PacienteIsos;
use App\PruebaSerologica;
use App\Service\FichaPacienteService;
use App\Service\MinsaService;
use App\Service\PruebaSerologicaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class PruebaSerologicaController extends Controller
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
        $paciente = PacienteIsos::find($request->id_paciente);
        $datos = [
            "nombres" => $paciente->nombres,
            "apellido_paterno" => $paciente->apellido_paterno,
            "apellido_materno" => $paciente->apellido_materno
        ];

        $codigo = "";
        foreach ($datos as $key => $value) {
            if ($value) {
                if (Str::isAscii($value)) $codigo .= $value[0];
                else $codigo .= $value[2];
            }
        }
        $codigo_ps = $codigo.substr($paciente->numero_documento, -2);

        $ps = new PruebaSerologica();
        $ps->idfichapacientes = $request->idficha_paciente;
        $ps->id_usuario = Auth::id();
        $ps->hash = $request->idficha_paciente . date('Ymd');
        $ps->codigo_ps = Str::upper($codigo_ps);
        $ps->save();

        return response(["ps" => $ps]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pruebas = PruebaSerologica::select('idpruebaserologicas','idfichapacientes','p1_positivo_recuperado','p1_react1gm','p1_reactigg'
            ,'p1_reactigm_igg','no_reactivo','created_at','p1_positivo_persistente','p1_positivo_vacunado')
            ->whereHas('fichapaciente', function ($q) use ($id) {
                return $q->where('id_paciente', $id);
            })
            ->where("invalido", 0)
            ->whereNotNull("no_reactivo")
            ->latest()
            ->get();

        if (!empty($pruebas)) {
            foreach ($pruebas as $ps) {
                try {
                    $ps->resultado = (new PruebaSerologicaService($ps))->result();
                    $ps->fecha = $ps->created_at->format('d/m/y');
                } catch (\Exception $ex) {
                    //no-op
                }
            }
        }

        return response($pruebas);
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
        if (PruebaSerologicaService::esValido($request->all())) {
            $ps = PruebaSerologica::findOrFail($id);
            $ps->ps_llamada_113 = $request->ps_llamada_113;
            $ps->ps_de_eess = $request->ps_de_eess;
            $ps->ps_contactocasocon = $request->ps_contactocasocon;
            $ps->ps_contactocasosos = $request->ps_contactocasosos;
            $ps->ps_personaext = $request->ps_personaext;
            $ps->ps_personalsalud = $request->ps_personalsalud;
            $ps->ps_otro = $request->ps_otro ;
            $ps->p1_react1gm = $request->p1_react1gm;
            $ps->p1_reactigg = $request->p1_reactigg;
            $ps->p1_reactigm_igg = $request->p1_reactigm_igg;
            $ps->no_reactivo = $request->no_reactivo;
            $ps->invalido = $request->invalido;
            $ps->ccs = $request->ccs;
            $ps->condicion_riesgo = $request->condicion_riesgo;
            $ps->condicion_riesgo_detalle = $request->condicion_riesgo_detalle;
            $ps->hora_fin = now();
            $ps->p1_positivo_recuperado = $request->p1_positivo_recuperado;
            $ps->p1_marca = $request->p1_marca;
            $ps->positivo_anterior = $request->positivo_anterior;
            $ps->fecha_positivo_anterior = $request->fecha_positivo_anterior;
            $ps->lugar_positivo_anterior = $request->lugar_positivo_anterior;
            $ps->p1_positivo_persistente = $request->p1_positivo_persistente;
            $ps->p1_positivo_vacunado = $request->p1_positivo_vacunado;
            $ps->save();

            return response([
                'message' => 'Prueba serológica guardada correctamente',
            ]);
        }

        return response([
            'message' => 'Ingrese un resultado válido'
        ], 401);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $ps = PruebaSerologica::findOrFail($id);
        $ps->delete();

        return response(['message' => 'Eliminado correctamente!']);
    }

    /**
     * Start the ps
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function start($id)
    {
        $ps = PruebaSerologica::find($id);
        $ps->hora_inicio = date('Y-m-d H:i:s');
        $ps->hash = $id;
        $ps->save();

        return response(['ps' => $ps]);
    }

    /**
     * End the ps
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function finish($id)
    {
        $ps = PruebaSerologica::find($id);
        $ps->hora_fin = date('Y-m-d H:i:s');
        $ps->save();

        return response(['finished' => $ps]);
    }

}
