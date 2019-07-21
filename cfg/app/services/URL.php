<?php
/**
 * Created by PhpStorm.
 * User: Kiala
 * Date: 09/06/2015
 * Time: 22:24
 */

namespace cfg\app\services;


class URL {

    public function transformToStr($int) {

        $out = $int + date("Y") . "." . substr(sha1(str_shuffle("ntoprog")), 0, 5);
        $number = $int + 12;

        return $out . "." . $number;
    }

    public function transformToNumber($str) {

        $number = explode(".", $str);

        return $number[0] - date("Y");
    }
}