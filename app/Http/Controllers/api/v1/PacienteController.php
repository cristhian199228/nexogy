<?php

namespace App\Http\Controllers\api\v1;

use App\DepartamentoUbigeo;
use App\DistritoUbigeo;
use App\FichaPaciente;
use App\Http\Controllers\Controller;
use App\PacienteIsos;
use App\ProvinciaUbigeo;
use App\Service\FichaPacienteService;
use App\Service\PruebaAntigenaService;
use App\Service\PruebaSerologicaService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use GuzzleHttp\Client;
use League\Flysystem\FileNotFoundException;

class PacienteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $pacientes = PacienteIsos::search($request->buscar)
            ->with("Empresa")
            ->with("DepartamentoUbigeo")
            ->with("ProvinciaUbigeo")
            ->with("DistritoUbigeo")
            ->paginate(15);

        $pagina = $pacientes->currentPage();
        $total = $pacientes->total();
        $contador = $total - (15*($pagina-1));

        foreach ($pacientes as $paciente) {
            $nom_completo = $paciente->nombres . " " .$paciente->apellido_paterno . " " .$paciente->apellido_materno;
            $paciente->nom_completo = Str::upper($nom_completo);
            $paciente->contador = $contador;
            $contador--;
        }

        return $pacientes;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $validate = Validator::make($request->all(), [
            'tipo_doc' => 'required|integer',
            'nro_doc' => 'required',
            'nombres' => 'required',
            'ap' => 'required',
            'am' => 'required_if:tipo_doc,1',
            'fecha_nac' => 'required|date',
            'sexo' => 'required',
            'id_dep' => 'required',
            'id_pro' => 'required',
            'id_dis' => 'required',
            'id_empresa' => 'required|integer',
            'puesto' => 'required',
        ]);

        if ($validate->fails()) {
            $respuesta = [
                'code' => 400,
                'message' => 'Error en campos requeridos',
                'errors' => $validate->errors(),
            ];
        } else {

            $registrado = PacienteIsos::where("idpacientes", $request->nro_doc)->first();

            if($registrado) {
                $respuesta = [
                    'code' => 400,
                    'message' => 'El Paciente ya esta registrado',
                    'paciente' => $registrado,
                ];

            } else {
                $nombres = Str::upper(eliminarEspacios($request->nombres));
                $ap = Str::upper(eliminarEspacios($request->ap));
                $am = null;
                if($request->am) $am = Str::upper(eliminarEspacios($request->am));
                $direccion = null;
                if ($request->direccion) $direccion = Str::title($request->direccion);

                $paciente = new PacienteIsos();
                $paciente->nombres = $nombres;
                $paciente->apellido_paterno = $ap;
                $paciente->apellido_materno = $am;
                $paciente->fecha_nacimiento =  $request->fecha_nac;
                $paciente->tipo_documento = $request->tipo_doc;
                $paciente->numero_documento = $request->nro_doc;
                $paciente->sexo = Str::upper($request->sexo);
                $paciente->residencia_departamento = $request->id_dep;
                $paciente->residencia_provincia = $request->id_pro;
                $paciente->residencia_distrito = $request->id_dis;
                $paciente->direccion = $direccion;
                $paciente->celular = $request->celular;
                $paciente->correo = Str::lower($request->correo);
                $paciente->idempresa = $request->id_empresa;
                $paciente->puesto = Str::title($request->puesto);
                $paciente->save();

                $respuesta = [
                    'code' => 200,
                    'message' => 'Paciente registrado correctamente',
                    'paciente' => $paciente,
                ];
            }
        }

        return response($respuesta, $respuesta['code']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $paciente = PacienteIsos::where("idpacientes",$id)
            ->with(["FichaPaciente" => function ($q) {
                $q->select('idficha_paciente','id_paciente','created_at')
                    ->with("DatosClinicos","AntecedentesEp","AnexoTres","DeclaracionJurada","ConsentimientoInformado","Temperatura",
                        "PcrPruebaMolecular.FichaInvestigacion.FichaInvFoto", "PcrPruebaMolecular.PcrFotoMuestra")
                    ->with(["PruebaSerologica" => function ($q) {
                        $q->select('idpruebaserologicas','idfichapacientes','p1_positivo_recuperado','p1_react1gm','p1_reactigg'
                            ,'p1_reactigm_igg','no_reactivo','created_at','p1_positivo_persistente','p1_positivo_vacunado','invalido')
                            ->where("invalido", 0)
                            ->whereNotNull("no_reactivo")
                            ->latest();
                    }])
                    ->with(["pruebaAntigena" => function ($q) {
                        $q->select('id','idficha_paciente','resultado','created_at')
                            ->whereIn("resultado", [0, 1])
                            ->latest();
                    }])
                    ->latest()
                    ->get();
            }])
            ->with("ExcelNicole")
            ->with('Empresa:idempresa,descripcion,nombrecomercial,ruc')
            ->first();

        $fichas = $paciente->FichaPaciente;

        foreach ($fichas as $ficha) {
            foreach ($ficha->PruebaSerologica as $ps) {
                try {
                    $ps->resultado = (new PruebaSerologicaService($ps))->result();
                    $ps->fecha = $ps->created_at->format('d/m/y');
                } catch (\Exception $ex) {

                }
            }

            if($ficha->PcrPruebaMolecular && $ficha->PcrPruebaMolecular->resultado !== null) {
                $ficha->PcrPruebaMolecular->fecha2 = Carbon::parse($ficha->PcrPruebaMolecular->created_at)->format('d/m/y');
            }

            $ficha->fecha2 = Carbon::parse($ficha->created_at)->format("d/m/y");
            $ficha->edad2 = Carbon::parse($paciente->fecha_nacimiento)->age;
        }

        $paciente->pruebas = FichaPacienteService::mergePruebas($fichas);
        $paciente->edad = Carbon::parse($paciente->fecha_nacimiento)->age;

        return $paciente;
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
        $validated = $request->validate([
            'tipo_documento' => 'required|integer',
            'numero_documento' => 'required',
            'nombres' => 'required',
            'apellido_paterno' => 'required',
            'apellido_materno' => 'required_if:tipo_doc,1',
            'fecha_nacimiento' => 'required|date_format:Y-m-d',
            'sexo' => 'required',
            'residencia_departamento' => 'required',
            'residencia_provincia' => 'required',
            'residencia_distrito' => 'required',
            'idempresa' => 'required|integer',
        ]);

        $paciente = PacienteIsos::findOrFail($id);
        $paciente->nombres = Str::upper($validated['nombres']);
        $paciente->apellido_paterno = Str::upper($validated['apellido_paterno']);
        $paciente->apellido_materno = $validated['apellido_materno'] ? Str::upper($validated['apellido_materno']) : null;
        $paciente->fecha_nacimiento = $validated['fecha_nacimiento'];
        $paciente->tipo_documento = $validated['tipo_documento'];
        $paciente->numero_documento = $validated['numero_documento'];
        $paciente->sexo = $validated['sexo'];
        $paciente->residencia_departamento = $validated['residencia_departamento'];
        $paciente->residencia_provincia = $validated['residencia_provincia'];
        $paciente->residencia_distrito = $validated['residencia_distrito'];
        $paciente->direccion = $request->direccion ?: null;
        $paciente->celular = $request->celular ?: null;
        $paciente->correo = $request->correo ? Str::lower($request->correo) : null;
        $paciente->idempresa = $validated['idempresa'];
        $paciente->puesto = $request->puesto ?: null;
        $paciente->save();

        return response(['message' => 'Actualizado correctamente']);
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
        PacienteIsos::findOrFail($id)->delete();

        return response(['message' => 'Eliminado correctamente']);
    }

    /**
     * Search patients
     *
     * @return \Illuminate\Http\Response
     */

    public function search(Request $request) {

        $pacientes = PacienteIsos::search($request->buscar)
            ->with('Empresa:idempresa,descripcion,nombrecomercial')
            ->with(['programacionCv' => function ($q) {
                $q->latest();
            }])
            ->with("DepartamentoUbigeo")
            ->with("ProvinciaUbigeo")
            ->with("DistritoUbigeo")
            ->take(10)
            ->get();

        foreach ($pacientes as $paciente) {
            $paciente->nom_completo = $paciente->nombres. " " . $paciente->apellido_paterno . " " .$paciente->apellido_materno;
        }

        return response($pacientes);
    }

    /**
     * Show the patient photo from the storage
     *
     * @param  string $path
     * @return \Illuminate\Http\Response
     */
    public function showPhoto(string $path) {

        $file = '/FP/' . $path;
        $file = Storage::disk('ftp')->get($file);
        return Image::make($file)->response();
    }

    /**
     * Busca al paciente en minsa o mediweb
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */

    public function searchMinsa(Request $request) {

        $numero_doc = $request->numero_documento;
        $tipo_doc = $request->tipo_documento;
        if(!$tipo_doc) $tipo_doc = 1;

        $client = new Client([
            'base_uri' => 'https://siscovid.minsa.gob.pe/',
        ]);

        $req = $client->get('ficha/api/buscar-documento/' . '0'. $tipo_doc. '/' . $numero_doc, [
            'connect_timeout' => 5.0,
            'max_retry_attempts' => 3,
        ]);

        $response = $req->getBody();
        $obj = json_decode($response, true);
        $dataMinsa = $obj['datos']['data'];

        if(count($dataMinsa) > 0) {
            $fecha_nac = $dataMinsa['fecha_nacimiento'];
            $sexo = $dataMinsa['sexo'];
            $celular = null;
            $latitud = null;
            $longitud = null;
            $correo = null;
            $id_dep = null;
            $nom_dep = null;
            $id_pro = null;
            $nom_pro = null;
            $id_dis = null;
            $nom_dis = null;
            $foto = null;
            $direccion = null;

            if (array_key_exists('celular', $dataMinsa) && $dataMinsa['celular']) $celular = $dataMinsa['celular'];
            if (array_key_exists('latitud', $dataMinsa) && $dataMinsa['latitud']) $latitud = $dataMinsa['latitud'];
            if (array_key_exists('longitud', $dataMinsa) && $dataMinsa['longitud']) $longitud = $dataMinsa['longitud'];
            if (array_key_exists('correo', $dataMinsa) && $dataMinsa['correo']) $correo = $dataMinsa['correo'];
            if (array_key_exists('residencia_departamento', $dataMinsa) && $dataMinsa['residencia_departamento']) {
                $id_dep = $dataMinsa['residencia_departamento'];
                $nom_dep = DepartamentoUbigeo::find($id_dep)->name;
            }
            if (array_key_exists('residencia_provincia', $dataMinsa) && $dataMinsa['residencia_provincia']) {
                $id_pro = $dataMinsa['residencia_provincia'];
                $nom_pro = ProvinciaUbigeo::find($id_pro)->name;
            }
            if (array_key_exists('residencia_distrito', $dataMinsa) && $dataMinsa['residencia_distrito']) {
                $id_dis = $dataMinsa['residencia_distrito'];
                $nom_dis = DistritoUbigeo::find($id_dis)->name;
            }
            if (array_key_exists('direccion', $dataMinsa) && $dataMinsa['direccion']) $direccion = $dataMinsa['direccion'];
            if (array_key_exists('foto', $dataMinsa) && $dataMinsa['foto']) $foto = $dataMinsa['foto'];

            if ($sexo === "1") $sexo = "M";
            else $sexo = "F";

            $datos = [
                'nombres' => $dataMinsa['nombres'],
                'apellido_paterno'=> $dataMinsa['apellido_paterno'],
                'apellido_materno' => $dataMinsa['apellido_materno'],
                'fecha_nacimiento' => Carbon::parse($fecha_nac)->format('Y-m-d'),
                'tipo_documento' => $tipo_doc,
                'numero_documento' => $numero_doc,
                'sexo' => $sexo,
                'nombre_departamento' => Str::upper($nom_dep),
                'id_departamento' => $id_dep,
                'nombre_provincia' => Str::upper($nom_pro),
                'id_provincia' => $id_pro,
                'nombre_distrito' => Str::upper($nom_dis),
                'id_distrito' => $id_dis,
                'direccion' => $direccion,
                'celular' => $celular,
                'correo' => $correo,
                'latitud' => $latitud,
                'longitud' => $longitud,
                'foto' => $foto,
            ];

            $respuesta = [
                'code' => 200,
                'message' => "Datos encontrados",
                'paciente' => $datos,
            ];

        } else {
            $respuesta = [
                'code' => 200,
                'message' => "No se encontraron datos",
            ];
        }

        return response($respuesta, $respuesta['code']);
    }

    /**
     * Register the patient from Minsa or Mediweb
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */

    public function storeMinsa(Request $request) {

        $numero_doc = $request->numero_documento;
        $tipo_doc = $request->tipo_documento;

        $client = new Client([
            'base_uri' => 'https://siscovid.minsa.gob.pe/',
        ]);

        $req = $client->get('ficha/api/buscar-documento/' . '0'. $tipo_doc. '/' . $numero_doc, [
            'connect_timeout' => 5.0,
            'max_retry_attempts' => 6,
        ]);

        $response = $req->getBody();
        $obj = json_decode($response, true);
        $dataMinsa = $obj['datos']['data'];

        if(count($dataMinsa) > 0) {
            $fecha_nac = null;
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
            $direccion = null;

            if (array_key_exists('fecha_nacimiento', $dataMinsa) && $dataMinsa['fecha_nacimiento']) {
                $fecha_nac = Carbon::parse($dataMinsa['fecha_nacimiento'])->format('Y-m-d');
            }
            if (array_key_exists('celular', $dataMinsa) && $dataMinsa['celular']) $celular = $dataMinsa['celular'];
            if (array_key_exists('direccion', $dataMinsa) && $dataMinsa['direccion'] && Str::length($dataMinsa['direccion']) > 15) {
                $direccion = Str::title($dataMinsa['direccion']);
            }
            if (array_key_exists('latitud', $dataMinsa) && $dataMinsa['latitud']) $latitud = $dataMinsa['latitud'];
            if (array_key_exists('longitud', $dataMinsa) && $dataMinsa['longitud']) $longitud = $dataMinsa['longitud'];
            if (array_key_exists('correo', $dataMinsa) && $dataMinsa['correo']) $correo = Str::lower($dataMinsa['correo']);
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

            if ($sexo == "1") $sexo = "M";
            else $sexo = "F";

            $paciente_minsa = new PacienteIsos();
            $paciente_minsa->nombres = Str::upper($dataMinsa['nombres']);
            $paciente_minsa->apellido_paterno = Str::upper($dataMinsa['apellido_paterno']);
            $paciente_minsa->apellido_materno = Str::upper($dataMinsa['apellido_materno']);
            $paciente_minsa->fecha_nacimiento =  $fecha_nac;
            $paciente_minsa->tipo_documento = $tipo_doc;
            $paciente_minsa->numero_documento = $numero_doc;
            $paciente_minsa->sexo = $sexo;
            $paciente_minsa->residencia_pais = $pais_res;
            $paciente_minsa->residencia_departamento = $dep_res;
            $paciente_minsa->residencia_provincia = $prov_res;
            $paciente_minsa->residencia_distrito = $dis_res;
            $paciente_minsa->direccion = $direccion;
            $paciente_minsa->celular = $celular;
            $paciente_minsa->correo = $correo;
            $paciente_minsa->latitud = $latitud;
            $paciente_minsa->longitud = $longitud;
            $paciente_minsa->foto = $foto;
            $paciente_minsa->idempresa = 258;
            $paciente_minsa->estado = 1;
            $paciente_minsa->save();

            $respuesta = [
                'code' => 200,
                'message' => 'Paciente registrado correctamente',
                'paciente' => $paciente_minsa
            ];

        } else {
            $respuesta = [
                'code' => 400,
                'message' => 'No se encontraron datos',
            ];
        }

        return response($respuesta, $respuesta['code']);
    }

    public function buscarPorDni(Request $request) {
        return PacienteIsos::firstWhere('numero_documento', $request->numero_documento);
    }

    public function updateCelular(Request $request) {

        $request->validate([
            'id_paciente' => 'required',
            'celular' => 'required|digits:9'
        ]);

        $paciente = PacienteIsos::findOrFail($request->id_paciente);
        $paciente->celular = $request->celular;
        $paciente->save();

        return response([
            'message' => 'Celular actualizado correctamente',
            'data' => $paciente->celular
        ]);
    }

}
