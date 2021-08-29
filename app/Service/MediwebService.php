<?php

namespace App\Service;

use App\FichaPaciente;
use Illuminate\Support\Facades\Http;

class MediwebService
{
    /**
     * @var FichaPaciente
     */
    private $ficha;
    private $fichaService;
    private const URL_API_MEDIWEB = 'http://10.10.10.11/apicovid.php';

    private const SINTOMAS_ARR = [
        "",
        "tos",
        "dolor_garganta",
        "congestion_nasal",
        "dificultad_respiratoria",
        "fiebre",
        "malestar_general",
        "diarrea",
        "nauseas_vomitos",
        "cefalea",
        "irritabilidad_confusion",
        "dolor_garganta",
        "falta_aliento",
        "anosmia_ausegia",
        "otros",
        "toma_medicamento"
    ];

    private const ANTECEDENTES_ARR = [
        "",
        "paises_visitados",
        "contacto_cercano",
        "conv_covid",
        "debilite_sistema"
    ];

    private $resPrs;
    private $resPcr;

    public function __construct(FichaPaciente $ficha, $resPrs = null, $resPcr = null)
    {
        $this->ficha = $ficha;
        $this->fichaService = new FichaPacienteService($ficha);
        $this->resPrs = $resPrs;
        $this->resPcr = $resPcr;
    }

    public function sendRequest() {
        $response = Http::post(self::URL_API_MEDIWEB, $this->getParams());
        return $response->body();
    }

    private function getSintomas() : array {
        $sintomas = [];

        for ($i = 1; $i < count(self::SINTOMAS_ARR); $i++) {
            $value = "NO";
            if ($this->fichaService->esSintomatico()) {
                $datosClinicos = $this->ficha->DatosClinicos[0];
                $value = $datosClinicos[self::SINTOMAS_ARR[$i]];
                if (is_numeric($value)) {
                    $value = $value ? "SI" : "NO";
                }
                if (is_null($value)) {
                    $value = "NO";
                }
            }
            $sintomas["Pregunta" . $i] = $value;
        }
        return $sintomas;
    }

    private function getAntecedentes() : array {
        $antecedentes = [];

        for ($i = 1; $i < count(self::ANTECEDENTES_ARR); $i++) {
            $value = "NO";
            if ($this->fichaService->esEpidemiologico()) {
                $antedentesEp = $this->ficha->AntecedentesEp[0];
                $value = $antedentesEp[self::ANTECEDENTES_ARR[$i]];
                if (is_numeric($value)) {
                    $value = $value ? "SI" : "NO";
                }
                if (is_null($value)) {
                    $value = "NO";
                }
            }
            $antecedentes["Ant_Pregunta" . $i] = $value;
        }
        return $antecedentes;
    }

    private function getTemperatura() {
        $temp = 36.6;
        if(count($this->ficha->Temperatura) > 0) {
            $temp =$this->ficha->Temperatura[0]->valortemp;
        }
        return $temp;
    }
    
    public function getParams() {
        
        $reqParamArray['DatosPaciente'] = array([
            "Tip_doc" => $this->ficha->PacienteIsos->tipo_documento,
            "Dni" => $this->ficha->PacienteIsos->numero_documento,
            "Ape_Pat" => $this->ficha->PacienteIsos->apellido_paterno,
            "Ape_Mat" => $this->ficha->PacienteIsos->apellido_materno,
            "Nombres" => $this->ficha->PacienteIsos->nombres,
            "Sexo" => $this->ficha->PacienteIsos->sexo,
            "Fecha_nac" => $this->ficha->PacienteIsos->fecha_nacimiento,
            "Ruc_Emp" => $this->ficha->PacienteIsos->Empresa->ruc,
            "Nom_Emp" => $this->ficha->PacienteIsos->Empresa->descripcion,
            "Ruc_Cont" => "",
            "Nom_Cont" => "",
            "Puesto" => $this->ficha->PacienteIsos->puesto,
            "Pais_Nac" => "",
            "Dep_Nac" => "",
            "Prov_Nac" => "",
            "Dis_Nac" => "",
            "Pais_Resi" => "",
            "Dep_Resi" => "",
            "Prov_Resi" => "",
            "Dis_Resi" => "",
            "Gerencia" => "",
            "Superintendencia" => "",
            "Codigows" => $this->ficha->idficha_paciente
        ]);
        $reqParamArray['DatosOrden'] = array([
            "Fecha" => date('Y-m-d'),
            "Hora" => date('H:i:s'),
            "PuntoAtencion" => "1"
        ]);

        $reqParamArray["DatosFormato"] = array(array_merge(
            $this->getSintomas(),
            $this->getAntecedentes(),
            [
                "Temperatura" => $this->getTemperatura(),
                "Resultado_Prueba" => $this->resPrs,
                "Resultado_Prueba2" => $this->resPcr
            ]
        ));

        return $reqParamArray;
    }
}