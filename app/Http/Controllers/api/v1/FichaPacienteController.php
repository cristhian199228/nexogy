<?php

namespace App\Http\Controllers\api\v1;

use App\AntecedentesEp;
use App\CitasMw;
use App\DatosClinicos;
use App\FichaPaciente;
use App\FichaTemporal;
use App\Http\Controllers\Controller;
use App\MensajeWhatsApp;
use App\PacienteIsos;
use App\Service\MediwebService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use function Matrix\trace;

class FichaPacienteController extends Controller
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
            'id_estacion' => 'required',
            'id_paciente' => 'required',
            'id_empresa' => 'required',
            'turno' => 'required',
            'rol' => 'required',
            'puesto' => 'required',
            'id_departamento' => 'required',
            'id_provincia' => 'required',
            'id_distrito' => 'required',
            'direccion' => 'required',
            'celular' => 'required',
            'enviar_mensaje' => 'required|boolean',
            'numero_verificado' => 'required|boolean',
        ]);

        $ficha = FichaPaciente::where('id_paciente', $request->id_paciente)
            ->where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->where("id_estacion", $request->id_estacion)
            ->first();

        if ($ficha) {
            return response([
                'message' => 'No puede crear dos atenciones del mismo paciente en la misma estación'
            ], 400);
        }

        $paciente = PacienteIsos::find($request->id_paciente);
        $paciente->residencia_departamento = $request->id_departamento;
        $paciente->residencia_provincia = $request->id_provincia;
        $paciente->residencia_distrito = $request->id_distrito;
        $paciente->idempresa = $request->id_empresa;
        $paciente->puesto = $request->puesto;
        $paciente->correo = $request->correo ? Str::lower($request->correo) : null;
        $paciente->celular = $request->celular;
        $paciente->direccion = $request->direccion;
        $paciente->save();

        $nueva_ficha = new FichaPaciente();
        $nueva_ficha->id_usuario = Auth::id();
        $nueva_ficha->id_paciente = $request->id_paciente;
        $nueva_ficha->id_estacion = $request->id_estacion;
        $nueva_ficha->id_empresa = $request->id_empresa;
        $nueva_ficha->hash = $request->id_estacion.$paciente->numero_documento.date('Ymd');
        $nueva_ficha->rol = $request->rol;
        $nueva_ficha->turno = $request->turno;
        $nueva_ficha->estado = 0;
        $nueva_ficha->enviar_mensaje = $request->enviar_mensaje;
        $nueva_ficha->numero_verificado = $request->numero_verificado;
        $nueva_ficha->save();

        return response([
            'message' => "Ficha creada correctamente",
            'ficha' => $nueva_ficha
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
        $fichas = FichaPaciente::where('id_paciente', $id)
            ->where(DB::raw('DATE(created_at)'), date('Y-m-d'))
            ->get();

        if (count($fichas) > 1) {
            $respuesta = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No se pueden crear mas de dos atenciones diarias para un paciente'
            ];
        } elseif (count($fichas) > 0) {
            $respuesta = [
                'code' => 200,
                'status' => 'success',
                'tiene_atencion' => true
            ];
        } else {
            $respuesta = [
                'code' => 200,
                'status' => 'success',
                'tiene_atencion' => false
            ];
        }
        return response($respuesta, $respuesta['code']);
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

    /**
     * Finish the atencion
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function finish($id)
    {
        $categoria = FichaPaciente::findOrFail($id);
        $categoria->hora_termino = date('H:i:s ');
        $categoria->estado = 1;
        $categoria->save();

        return response(["message" => "Finalizado correctamente"]);
    }

    /**
     * Update turn
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateTurno(Request $request, $id)
    {
        //
        $ficha = FichaPaciente::find($id);
        $ficha->turno = $request->turno;
        $ficha->save();

        return response(["message" => "Se cambió el turno correctamente"]);
    }
}
