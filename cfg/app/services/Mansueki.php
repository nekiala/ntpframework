<?php

namespace cfg\app\services;

/**
 * Description of Mansueki
 *
 * @author Kiala
 */
class Mansueki {

    const DIRECTION = 'lukento';
    const DUEKA = '.';
    const SUKA = "[";

    private $funisa = 3;
    private static $mansueki = array();
    private static $mazayusu = array();

    public function tulaFunisa($funisa) {
        $this->funisa = $funisa;
    }

    public function bakaFunisa() {
        return $this->funisa;
    }

    private function tombaFunusa($mot) {
        return substr($mot, strrpos($mot, self::DUEKA) + 1);
    }

    private function yenaFunisa() {
        
    }

    public function sueka($mot) {
        $bfunusa = (int) $this->tombaFunusa($mot);
        $funusu = $bfunusa > 0 ? $bfunusa : $this->bakaFunisa();

        $ntangulu = substr($mot, 0, strrpos($mot, self::DUEKA) + 1);

        $kintangulu = strlen($ntangulu);

        for ($i = 0; $i < $kintangulu; $i ++) {
            self::$mansueki[] = ord(($ntangulu[$i])) * $funusu . self::SUKA;
        }

        return implode("", self::$mansueki) . "." . $funusu;
    }

    public function zayisa($mot) {
        
        $bfunusa = (int) $this->tombaFunusa($mot);
        $funusu = $bfunusa > 0 ? $bfunusa : $this->bakaFunisa();

        $ntangulu = substr($mot, 0, strrpos($mot, self::DUEKA) + 1);

        $kintangulu = explode(self::SUKA, $ntangulu);

        for ($i = 0, $c = count($kintangulu) - 1; $i < $c; $i ++) {
            self::$mazayusu[] = chr($kintangulu[$i] / $funusu);
        }

        return implode("", self::$mazayusu) . $funusu;
    }

}
