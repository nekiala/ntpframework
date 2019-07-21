<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace cfg\app\services;

/**
 * Ce service create an authorization number while creating a system
 *
 * @author Kiala
 */
class Authnumber {

    private $code = "";

    public function generate($count = 3) {

        $this->code .= $this->code() . "-";
        $count--;

        if ($count > 0) {
            return $this->generate($count);
        }

        $code = trim($this->code, "-");

        return strtoupper($code);
    }

    private function code() {

        return substr(str_shuffle(sha1("ntp")), 0, 4);
    }
}
