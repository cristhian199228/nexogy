<?php

namespace App\Service;

use App\PruebaAntigena;

class PruebaAntigenaService {
    private $pa;
    private const RESULTADOS_STR = ['NO REACTIVO', 'REACTIVO', 'INVALIDO', 'INDETERMINADO'];

    private const RESULTADOS_CV = [];

    public function __construct(PruebaAntigena $pruebaAntigena)
    {
        $this->pa = $pruebaAntigena;
    }

    /**
     * @return PruebaAntigena
     */
    public function getPa(): PruebaAntigena
    {
        return $this->pa;
    }

    public function strResult() : string {
        return self::RESULTADOS_STR[$this->getPa()->resultado];
    }

}