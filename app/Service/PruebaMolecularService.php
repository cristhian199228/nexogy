<?php

namespace App\Service;

use App\FichaPaciente;
use App\PcrPruebaMolecular;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PruebaMolecularService {

    private const RESULTADOS_STR = ['NEGATIVO', 'POSITIVO', 'ANULADO'];
    private $pcr;

    public function __construct(PcrPruebaMolecular $pcr)
    {
        $this->pcr = $pcr;
    }

    public function strResult() : string {
        return self::RESULTADOS_STR[$this->pcr['resultado']];
    }

    public function whatsAppResult (): array  {
        if ($this->pcr['resultado'] == 2) throw new Exception('Resultado anulado');

        if ($this->pcr['resultado']) {
            return [
                'resultado' => '🔴 POSITIVO',
                'comentario' => 'En breve lo contactarán para generar las indicaciones complementarias, '.
                    'dentro de las que se encuentra la necesidad de aislamiento.'
            ];
        }
        return [
            'resultado' => '🟢 NEGATIVO',
            'comentario' => 'Su resultado implica que no presenta la enfermedad COVID-19, pero debe continuar'.
                ' con las medidas de bioseguridad de forma permanente.'
        ];
    }
}