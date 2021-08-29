<?php

namespace App\Http\Controllers;

use App\DatosCLinicos;
use App\Exports\ExportReporteSalud;
use App\Exports\ExportSalud;
use App\FichaPaciente;
use App\Paciente;
use App\PacienteIsos;
use App\PacientePorRegistrar;
use App\PcrPruebaMolecular;
use App\PruebaSerologica;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\DB;
use DateTime;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use GuzzleHttp\Client;

class SaludController extends Controller
{
    //

    public function downloadFileFP(Request $request){

        $file = '/FP/' . $request->file;
        $file = Storage::disk('ftp')->get($file);

        return Image::make($file)->response();
    }

    public function prueba(Request $request) {

        /*$fichas = DB::table('fichapacientes')
            ->select(DB::raw('DATE(fichapacientes.created_at) as fecha_atencion'),'pacientes.numero_documento','pacientes.nombres','pacientes.apellido_paterno','pacientes.apellido_materno',
                'estaciones.idestaciones','pruebaserologicas.*')
            ->join('pacientes', 'pacientes.numero_documento', '=', 'fichapacientes.dni')
            ->join('estaciones','estaciones.idestaciones','=','fichapacientes.id_estacion')
            ->leftJoin('datoclinicos','datoclinicos.idfichapacientes','=','fichapacientes.idficha_paciente')
            ->leftJoin('aepidemologicos','aepidemologicos.idfichapacientes','=','fichapacientes.idficha_paciente')
            ->leftJoin('pcr_pruebas_moleculares','pcr_pruebas_moleculares.idficha_paciente','=','fichapacientes.idficha_paciente')
            ->leftJoin('pruebaserologicas', function ($q) {
                $q->on('pruebaserologicas.idfichapacientes', '=', 'fichapacientes.idficha_paciente')
                    ->where("invalido", 0)
                    ->whereNotNull("no_reactivo")
                    ->latest();
            })
            ->where(DB::raw('DATE(fichapacientes.created_at)'), '2020-08-01')
            ->latest('fichapacientes.created_at')
            ->get();

        foreach ($fichas as $ficha) {
            $ficha->resultado_prs = getPrsResult($ficha);
            $ficha->nom_completo = $ficha->nombres. " " . $ficha->apellido_paterno. " " .$ficha->apellido_materno;
        }

       return $fichas;
        $dni = $request->dni;
        $paciente = PacienteIsos::where('numero_documento',$dni)->first();
        $manual = 0;
        $idtipodocumento = $request->id;

        if (!$paciente) {
            $estado = 0;

            $client = new Client([
                'base_uri' => 'https://siscovid.minsa.gob.pe/',
            ]);

            $request2 = $client->get('ficha/api/buscar-documento/' . '0'.$idtipodocumento. '/' . $dni, [
                'connect_timeout' => 5.0,
                'max_retry_attempts' => 5,
            ]);

            $response = $request2->getBody();
            $obj = json_decode($response, true);
            $dataMinsa = $obj['datos']['data'];

            if (count($dataMinsa) > 0) {
                $estado = 1;
                $fecha_nac = $dataMinsa['fecha_nacimiento'];
                $sexo = $dataMinsa['sexo'];
                $celular = null;
                $latitud = null;
                $longitud = null;
                $correo = null;
                $pais_res = null;
                $dep_res = null;
                $prov_res = null;
                $dis_res = null;
                $foto = null;

                if (array_key_exists('celular', $dataMinsa) && $dataMinsa['celular']) $celular = $dataMinsa['celular'];
                if (array_key_exists('latitud', $dataMinsa) && $dataMinsa['latitud']) $latitud = $dataMinsa['latitud'];
                if (array_key_exists('longitud', $dataMinsa) && $dataMinsa['longitud']) $longitud = $dataMinsa['longitud'];
                if (array_key_exists('correo', $dataMinsa) && $dataMinsa['correo']) $correo = $dataMinsa['correo'];
                if (array_key_exists('residencia_pais', $dataMinsa) && $dataMinsa['residencia_pais']) $pais_res = $dataMinsa['residencia_pais'];
                if (array_key_exists('residencia_departamento', $dataMinsa) && $dataMinsa['residencia_departamento']) $dep_res = $dataMinsa['residencia_departamento'];
                if (array_key_exists('residencia_provincia', $dataMinsa) && $dataMinsa['residencia_provincia']) $prov_res = $dataMinsa['residencia_provincia'];
                if (array_key_exists('residencia_distrito', $dataMinsa) && $dataMinsa['residencia_distrito']) $dis_res = $dataMinsa['residencia_distrito'];
                if (array_key_exists('foto', $dataMinsa) && $dataMinsa['foto']) {
                    $foto_base64 = $dataMinsa['foto'];
                    $imageName = Str::random(10) . '.' . 'jpg';
                    $foto = md5($imageName . time()) . '.jpg';
                    Storage::disk('ftp')->put('/FP/' . $foto,  base64_decode($foto_base64));
                }

                if ($sexo === "1") $sexo = "M";
                else $sexo = "F";

                if (!strpos($fecha_nac, "-")) {
                    $anio = substr($fecha_nac, 0, 4);
                    $mes = $fecha_nac[4] . $fecha_nac[5];
                    $dia = substr($fecha_nac, 6, 7);
                    $fecha_nac = $anio . "-" . $mes . "-" . $dia;
                }

                $paciente_minsa = new PacienteIsos();
                $paciente_minsa->nombres = $dataMinsa['nombres'];
                $paciente_minsa->apellido_paterno = $dataMinsa['apellido_paterno'];
                $paciente_minsa->apellido_materno = $dataMinsa['apellido_materno'];
                $paciente_minsa->fecha_nacimiento = $fecha_nac;
                $paciente_minsa->tipo_documento = $idtipodocumento;
                $paciente_minsa->numero_documento = $dni;
                $paciente_minsa->sexo = $sexo;
                $paciente_minsa->residencia_pais = $pais_res;
                $paciente_minsa->residencia_departamento = $dep_res;
                $paciente_minsa->residencia_provincia = $prov_res;
                $paciente_minsa->residencia_distrito = $dis_res;
                $paciente_minsa->direccion = $dataMinsa['direccion'];
                $paciente_minsa->celular = $celular;
                $paciente_minsa->correo = $correo;
                $paciente_minsa->latitud = $latitud;
                $paciente_minsa->longitud = $longitud;
                $paciente_minsa->foto = $foto;
                //$paciente_minsa->idempresa = $idempresa;
                $paciente_minsa->save();
                $id_paciente = $paciente_minsa->idpacientes;

            } else {
                $paciente_mw = Paciente::select('sexo','correo','idtipodocumento',
                    'celular','nombres','apellido_paterno','apellido_materno','dni2','direccion',
                    'fechanacimiento','empresa','puesto','iddepartamento','idprovincia','iddistrito')
                    ->where('dni2', $dni)
                    ->first();
                if($paciente_mw) {
                    $estado = 1;

                    $tipo_doc_mw = $paciente_mw->idtipodocumento;
                    $tipo_doc = 1;
                    if ($tipo_doc_mw === 1 && strlen($paciente_mw->dni2) === 8) {
                        $tipo_doc = 1;
                    } elseif ($tipo_doc_mw === 1 && strlen($paciente_mw->dni2) === 9) {
                        $tipo_doc = 7;
                    } elseif ($tipo_doc_mw === 2) {
                        $tipo_doc = 3;
                    } elseif ($tipo_doc_mw === 3) {
                        $tipo_doc = 2;
                    }

                    $iddepartamento = $paciente_mw->iddepartamento;
                    $idprovincia = $paciente_mw->idprovincia;
                    $iddistrito = $paciente_mw->iddistrito;

                    if ($iddepartamento) {
                        $iddepartamento = "0" . $paciente_mw->iddepartamento;
                    } else {
                        $iddepartamento = null;
                    }

                    if ($idprovincia) {
                        $idprovincia = "0" . $paciente_mw->idprovincia;
                    } else {
                        $idprovincia = null;
                    }

                    if ($iddistrito) {
                        $iddistrito = "0" . $paciente_mw->iddistrito;
                    } else {
                        $iddistrito = null;
                    }

                    $paciente_isos = new PacienteIsos();
                    $paciente_isos->nombres = Str::upper($paciente_mw->nombres);
                    $paciente_isos->apellido_paterno = Str::upper($paciente_mw->apellido_paterno);
                    $paciente_isos->apellido_materno = Str::upper($paciente_mw->apellido_materno);
                    $paciente_isos->fecha_nacimiento = $paciente_mw->fechanacimiento;
                    $paciente_isos->tipo_documento = $tipo_doc;
                    $paciente_isos->numero_documento = $dni;
                    $paciente_isos->sexo = $paciente_mw->sexo;
                    $paciente_isos->residencia_departamento = $iddepartamento;
                    $paciente_isos->residencia_provincia = $idprovincia;
                    $paciente_isos->residencia_distrito = $iddistrito;
                    $paciente_isos->direccion = $paciente_mw->direccion;
                    $paciente_isos->celular = $paciente_mw->celular;
                    $paciente_isos->correo = Str::lower($paciente_mw->correo);
                    $paciente_isos->estado = 1;
                    //$paciente_isos->idempresa = $idempresa;
                    $paciente_isos->save();
                    $id_paciente = $paciente_isos->idpacientes;
                } else {
                    $manual = 1;
                }
            }

            $pac_pendiente = new PacientePorRegistrar();
            $pac_pendiente->numero_documento = $dni;
            $pac_pendiente->tipo_documento = $idtipodocumento;
            //$pac_pendiente->idempresa = $idempresa;
            $pac_pendiente->estado = $estado;
            $pac_pendiente->save();
        }*/
    }
}
