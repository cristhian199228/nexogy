<?php

namespace App\Http\Controllers\api\v1;

use App\Empresa;
use App\Http\Controllers\Controller;
use App\Imports\ProgramacionDiariaCvImport;
use App\PacienteIsos;
use App\ProgramacionDiariaCv;
use App\Service\MinsaService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ProgramacionDiariaCvController extends Controller
{
    //
    public function import(Request $request) {
        (new ProgramacionDiariaCvImport())->import($request->file('programacion'));
        return response(['message' => 'Se importó correctamente']);
    }

    public function procesar() {
        $programados = ProgramacionDiariaCv::where('estado', 0)->get();

        foreach ($programados as $programado) {
            $nroDocCv = trim($programado->numero_documento);
            $tipoDocCv = trim($programado->tipo_documento);
            $paciente = PacienteIsos::where('numero_documento', $nroDocCv);
            $empresa = Empresa::where('ruc', $programado->ruc)->first();
            $idEmpresa = $empresa ? $empresa->idempresa : 258;
            $nroRegistroCv = $programado->registro ?: null;
            $puestoCv = $programado->puesto ? Str::upper($programado->puesto) : null;
            $estado = 0;

            if ($paciente->first()) {
                $estado = 1;
                $paciente->update([
                    'puesto' => $puestoCv,
                    'idempresa' => $idEmpresa,
                    'nro_registro' => $nroRegistroCv
                ]);
            } else {
                $url = 'https://siscovid.minsa.gob.pe/ficha/api/buscar-documento';
                $nroDocMinsa = preg_replace('/[^a-z0-9]+/i', '', $nroDocCv);

                for ($i = 1; $i <= 3; $i++) {
                    $tipoDocMinsa = '0' . $i;
                    try {
                        $request = Http::retry(3, 5000)->get("$url/$tipoDocMinsa/$nroDocMinsa");
                        if ($request->successful()
                            && isset($request->json()['datos']['data'])
                            && count($request->json()['datos']['data']) > 0) {
                            // se encontro paciente en minsa
                            $estado = 1;
                            $service = new MinsaService($request->json()['datos']['data']);
                            PacienteIsos::create([
                                'nombres' => $service->getValue('nombres'),
                                'apellido_paterno' => $service->getValue('apellido_paterno'),
                                'apellido_materno' => $service->getValue('apellido_materno'),
                                'fecha_nacimiento' => $service->getFechaNacimiento(),
                                'tipo_documento' => $tipoDocCv,
                                'numero_documento' => $nroDocCv,
                                'sexo' => $service->getSexo(),
                                'residencia_pais' => $service->getValue('residencia_pais'),
                                'residencia_departamento' => $service->getValue('residencia_departamento'),
                                'residencia_provincia' => $service->getValue('residencia_provincia'),
                                'residencia_distrito' => $service->getValue('residencia_distrito'),
                                'direccion' => $service->getValue('direccion'),
                                'celular' => $service->getValue('celular'),
                                'correo' => $service->getCorreo(),
                                'latitud' => $service->getValue('latitud'),
                                'longitud' => $service->getValue('longitud'),
                                'foto' => $service->getFoto(),
                                'estado' => $service->getEstado(),
                                'idempresa' => $idEmpresa,
                                'nro_registro' => $nroRegistroCv,
                                'puesto' => $puestoCv
                            ]);
                            break;
                        } else {
                            $estado = 2;
                        }
                    } catch (RequestException $exception) {
                        //
                        $estado = 2;
                    }
                }
            }
            ProgramacionDiariaCv::where('id', $programado->id)->update(['estado' => $estado]);
        }

    }
}
