<?php

namespace App\Service;

use App\PacienteIsos;
use App\PruebaSerologica;
use Exception;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class PruebaSerologicaService {
    
    private $ps;
    private const RESULTADOS_STR = ['', 'NO REACTIVO', 'IGG RECUPERADO', 'IGG VACUNADO', 'IGG', 'IGM', 'IGM/IGG',
            'IGM PERSISTENTE', 'IGM/IGG PERSISTENTE', 'INVALIDO'];

    private const RESULTADOS_CV = ['', 'NR','RR','RR','RR','RN','RN','RP','RP'];

    private const RESULTADOS_COLORES = ['', 'rgb(60, 179, 113)','rgb(60, 179, 113)','rgb(60, 179, 113)'
            ,'rgb(60, 179, 113)','rgb(255, 0, 0)','rgb(255, 0, 0)','rgb(60, 179, 113)','rgb(60, 179, 113)','rgb(255, 165, 0)'];

    //private const DIR = 'storage/fotos_temporal/';
    private const DIR =  'stickers/';
    private const DIR2 = 'public/storage/fotos_temporal/';
    private $fichaService;

    public function __construct(PruebaSerologica $ps)
    {
        $this->ps = $ps;
        $this->fichaService = new FichaPacienteService($ps->FichaPaciente);
    }

    public function getStickerShortPath() : string {
        return self::DIR . $this->ps->idpruebaserologicas . '/';
    }

    public function getStickerPath() : string {
        return public_path(self::DIR . $this->ps->idpruebaserologicas . '/');
    }
    
    public static function esValido($ps) : bool {
        $no_reactivo = $ps['no_reactivo'];
        $igg = $ps['p1_reactigg'];
        $igm = $ps['p1_react1gm'];
        $igm_igg = $ps['p1_reactigm_igg'];
        $recuperado = $ps['p1_positivo_recuperado'];
        $persistente = $ps['p1_positivo_persistente'];
        $vacunado = $ps['p1_positivo_vacunado'];
        $invalido = $ps['invalido'];

        if ($no_reactivo  && !$igg && !$recuperado && !$igm && !$igm_igg && !$invalido && !$persistente && !$vacunado) return true; //NO REACTIVO
        if (!$no_reactivo && $igg && $recuperado && !$igm && !$igm_igg && !$invalido && !$persistente && !$vacunado) return true; //IGG RECUPERADO
        if (!$no_reactivo && $igg && !$recuperado && !$igm && !$igm_igg && !$invalido && !$persistente && $vacunado) return true; //IGG VACUNADO
        if (!$no_reactivo && $igg && !$recuperado && !$igm && !$igm_igg && !$invalido && !$persistente && !$vacunado) return true; //IGG
        if (!$no_reactivo && !$igg && !$recuperado && $igm && !$igm_igg && !$invalido && !$persistente && !$vacunado) return true; //IGM
        if (!$no_reactivo && !$igg && !$recuperado && !$igm && $igm_igg && !$invalido && !$persistente && !$vacunado) return true; //IGM/IGG
        if (!$no_reactivo && !$igg && !$recuperado && !$igm && !$igm_igg && $invalido && !$persistente && !$vacunado) return true; //INVALIDO
        if (!$no_reactivo && !$igg && !$recuperado && $igm && !$igm_igg && !$invalido && $persistente && !$vacunado) return true; //IGM PERSISTENTE
        if (!$no_reactivo && !$igg && !$recuperado && !$igm && $igm_igg && !$invalido && $persistente && !$vacunado) return true; //IGM/IGG PERSISTENTE

        return false;
    }

    public function result() : int {

        if ($this->ps['no_reactivo']) return 1; //NO REACTIVO
        if ($this->ps['p1_reactigg'] && $this->ps['p1_positivo_recuperado']) return 2; //IGG RECUPERADO
        if ($this->ps['p1_reactigg'] && $this->ps['p1_positivo_vacunado']) return 3; //IGG VACUNADO
        if ($this->ps['p1_reactigg'] && !$this->ps['p1_positivo_recuperado'] && !$this->ps['p1_positivo_vacunado']) return 4; //IGG
        if ($this->ps['p1_react1gm'] && !$this->ps['p1_positivo_persistente']) return 5; //IGM
        if ($this->ps['p1_reactigm_igg'] && !$this->ps['p1_positivo_persistente']) return 6; //IGM/IGG
        if ($this->ps['p1_react1gm'] && $this->ps['p1_positivo_persistente']) return 7; //IGM PERSISTENTE
        if ($this->ps['p1_reactigm_igg'] && $this->ps['p1_positivo_persistente']) return 8; //IGM/IGG PERSISTENTE

        return 9;
    }

    public function cvResult() : string {
        return self::RESULTADOS_CV[$this->result()];
    }

    public function diasBloqueo() : int {
        switch ($this->result()) {
            case 5:
            case 6: return 6;
            default: return 0;
        }
    }

    public function diasBloqueoPcr() : int {
        switch ($this->result()) {
            case 5:
            case 6:
            case 7:
            case 8: return 6;
            default: return 0;
        }
    }

    public function strResult() : string {
        return self::RESULTADOS_STR[$this->result()];
    }

    public function color() : string {
        return $this->fichaService->esSintomatico() || $this->fichaService->esEpidemiologico() ?
                'rgb(255, 0, 0)' : self::RESULTADOS_COLORES[$this->result()];
    }

    private function paciente() {
        return $this->ps->FichaPaciente->PacienteIsos;
    }

    public function saveImageQueue() : void   {

        if (!File::exists($this->getStickerPath())) {
            File::makeDirectory($this->getStickerPath(), 0755, true, true);
        }

        $this->drawImage()->save($this->getStickerPath() . 'resultado.jpg');
    }

    public function saveImageConstancias() : void
    {
        if (!File::exists($this->getStickerShortPath())) {
            File::makeDirectory($this->getStickerShortPath(), 0755, true, true);
        }

        $this->drawImage()->save($this->getStickerShortPath() . 'resultado.jpg');
    }

    public function drawImage(): \Intervention\Image\Image {
        $stickerService = new StickerService();
        $stickerService->drawCircle($this->color());
        $stickerService->drawText($this->fichaService->rol(), 55, 27);
        $stickerService->drawText($this->paciente()->numero_documento, 90, 25);
        $stickerService->drawMultiLineText($this->paciente()->full_name, 120, 20, 16);
        $stickerService->drawMultiLineText($this->whatsAppResult()['clasificacion'], 170, 30, 20);
        $stickerService->drawText($this->ps->created_at->format('d/m/Y'), 260, 22);

        return $stickerService->getImg();
    }

    public function whatsAppResult (): array {

        $resultado = '🟡 NO VÁLIDO';
        $clasificacion = 'DEBE REPETIR PRUEBA';
        $comentario = 'Su resultado implica la repetición de la Prueba Serológica. Debe permanecer en la Sala donde le han indicado que espere.';

        switch ($this->result()){
            case 1:
                $resultado = '🟢 NO REACTIVO(NEGATIVO)';
                $clasificacion = 'PUEDE CONTINUAR CON EL PROCESO DE INGRESO A MINA';
                $comentario = 'Su resultado le permite continuar con el proceso de ingreso al entorno laboral.'
                        . 'Debe solicitar su sticker en el punto de control. Continuar con las medidas de bioseguridad de forma permanente.';
                break;
            case 4:
            case 2:
                $resultado = '🟢 REACTIVO PERSISTENTE(POSITIVO RECUPERADO)';
                $clasificacion =  'PUEDE CONTINUAR CON EL PROCESO DE INGRESO A MINA';
                $comentario = 'Su resultado es compatible con una condición médica de recuperación, por lo que puede '.
                    'continuar con el proceso para el ingreso al entorno laboral. Debe solicitar su sticker en el punto de control.';
                break;
            case 3:
                $resultado = '🟢 REACTIVO PERSONA VACUNADA(POSITIVO VACUNADO)';
                $clasificacion =  'PUEDE CONTINUAR CON EL PROCESO DE INGRESO A MINA';
                $comentario = 'Su resultado le permite continuar con el proceso de ingreso al entorno laboral. ' .
                    'Debe solicitar su sticker en el punto de control. Continuar con las medidas de bioseguridad de forma permanente.';
                break;
            case 5:
            case 6:
                $resultado = '🔴 REACTIVO(POSITIVO)';
                $clasificacion =  'DEBE PERMANECER EN SALA DE ESPERA';
                $comentario = 'En breve será contactado por el Área Médica para generar el proceso complementario. ' .
                    'Debe permanecer en la Sala donde le han indicado que espere.';
                break;
            case 7:
            case 8:
                $resultado = '🟢 REACTIVO PERSISTENTE(POSITIVO RECUPERADO)';
                $clasificacion =  'PUEDE CONTINUAR CON EL PROCESO DE INGRESO A MINA';
                $comentario = 'Su resultado es compatible con una condición médica de recuperación. Será contactado ' .
                    'si no ha pasado la Prueba Molecular, para continuar con el proceso de ingreso al entorno laboral. '.
                    'Si ya pasó la Prueba Molecular, debe solicitar su sticker en el punto de control. Queda pendiente '.
                    'la notificación del resultado de la Prueba Molecular.';
                break;
        }

        if($this->fichaService->esSintomatico()) {
            $resultado .= "/PRESENTA SINTOMATOLOGIA";
            $clasificacion = "DEBE PERMANECER EN SALA DE ESPERA";
            $comentario = "En breve será contactado por el Área Médica para generar el proceso complementario. Debe permanecer
            en la Sala donde le han indicado que espere.";
        }

        if($this->fichaService->esEpidemiologico()) {
            $resultado .= "/PRESENTA ANTECEDENTES EPIDEMIOLÓGICOS";
            $clasificacion = "DEBE PERMANECER EN SALA DE ESPERA";
            $comentario = "En breve será contactado por el Área Médica para generar el proceso complementario. Debe permanecer
            en la Sala donde le han indicado que espere.";
        }

        return [
            'resultado' => $resultado,
            'clasificacion' => $clasificacion,
            'comentario' => $comentario
        ];
    }
}
