<?php

namespace App\Service;

use App\FichaPaciente;

class FichaPacienteService {
    private const ROLES = ['', 'ACUARTELADO', 'ITINERANTE', 'NO APLICA'];
    private const TURNOS = ['', 'SUBIDA', 'BAJADA', 'NO APLICA'];

    /**
     * @var FichaPaciente
     */
    private $ficha;

    public function __construct(FichaPaciente $ficha)
    {
        $this->ficha = $ficha;
    }

    public function rol() : string {
        return self::ROLES[$this->ficha->rol];
    }

    public function turno() :string {
        return self::TURNOS[$this->ficha->turno];
    }

    public function esSintomatico() : bool {
        return count($this->ficha->DatosClinicos) > 0;
    }

    public function esEpidemiologico() : bool {
        return count($this->ficha->AntecedentesEp) > 0;
    }

    public static function mergePruebas($fichas) {

        $prs = $fichas->filter(function ($ficha) {
            return count($ficha->PruebaSerologica) > 0;
        })->map(function ($ficha) {
            return $ficha->PruebaSerologica[0];
        })->map(function ($ps) {
            return [
                'id' => $ps->idpruebaserologicas,
                'tipo' => 'PRS',
                'resultado' => (new PruebaSerologicaService($ps))->result(),
                'fecha' => $ps->created_at,
            ];
        });

        $pcr = $fichas->filter(function ($ficha) {
            return $ficha->PcrPruebaMolecular && $ficha->PcrPruebaMolecular->resultado !== null;
        })->map(function ($ficha) {
            return $ficha->PcrPruebaMolecular;
        })->map(function ($pcr) {
            return [
                'id' => $pcr->idpcr_pruebas_moleculares,
                'tipo' => 'PCR',
                'resultado' => $pcr->resultado,
                'fecha' => $pcr->created_at,
            ];
        });

        $ag = $fichas->filter(function ($ficha) {
            return count($ficha->pruebaAntigena) > 0;
        })->map(function ($ficha) {
            return $ficha->pruebaAntigena[0];
        })->map(function ($pa) {
            return [
                'id' => $pa->id,
                'tipo' => 'AG',
                'resultado' => $pa->resultado,
                'fecha' => $pa->created_at,
            ];
        });

        //$merged = $prs->merge($pcr)->merge($ag)->all();
        $merged = array_merge($prs->all(), $pcr->all(), $ag->all());
        usort($merged, function ($a, $b) {
            return strtotime($b["fecha"]) - strtotime($a["fecha"]);
        });

        return $merged;
    }
}