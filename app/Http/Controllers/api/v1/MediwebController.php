<?php

namespace App\Http\Controllers\api\v1;

use App\CitasMw;
use App\Empresa;
use App\FichaPaciente;
use App\Http\Controllers\Controller;
use App\Paciente;
use App\PacienteIsos;
use App\PcrPruebaMolecular;
use App\Service\MediwebService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MediwebController extends Controller
{
    public function crearCita(Request $request)
    {
        $request->validate([
            'idficha_paciente' => 'required',
            'estado_prs' => 'required'
        ]);

        $ficha = FichaPaciente::where("idficha_paciente",$request->idficha_paciente)
            ->with(["PruebaSerologica" => function ($q) {
                $q->where("invalido", 0)
                    ->whereNotNull("no_reactivo")
                    ->latest();
            }])
            ->firstOrFail();

        $service = new MediwebService($ficha, $request->estado_prs);
        $result = $service->sendRequest();

        if($result) {
            $cita = new CitasMw();
            $cita->idficha_paciente = $ficha->idficha_paciente;
            $cita->tipo = 1;
            $cita->cadena_envio = json_encode($service->getParams());
            $cita->respuesta_ws = json_encode($result);
            $cita->id_usuario = Auth::id();
            $cita->save();

            return response([
                'message' => 'Se creó la cita correctamente',
            ]);
        }

        return response([
            'message' => 'Error al enviar a web service',
        ], 401);
    }
}
