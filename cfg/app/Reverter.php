<?php
/**
 * Created by PhpStorm.
 * User: Kiala
 * Date: 25/06/2015
 * Time: 11:20
 */

namespace cfg\app;


class Reverter {

    public static function doRevert($name) {
        $mathes = null;

        preg_match('`^[a-z]+(_[a-z]{1})(.+)$`i', $name, $mathes);

        if (!$mathes) {

            return ucfirst($name);
        }


        $name_formatted = str_replace('_id', '', $name);

        $final = str_replace($mathes[1], strtoupper($mathes[1][1]), $name_formatted);

        if (preg_match('`[a-z]+(_[a-z]{1})(.+)$`i', $final)) {

            return self::doRevert($final);
        }

        return ucfirst($final);
    }

    public static function doReplacements($table_name) {

        $matches = range('A', 'Z');

        foreach ($matches as $match) {
            $table_name = str_replace($match, '_' . $match, $table_name);
        }

        return strtolower(trim($table_name, '_'));
    }

    public static function doAllRevert($name) {

        $mathes = null;

        preg_match('`(_[a-z]{1})(.+)$`', $name, $mathes);

        if (!$mathes) {
            return ucfirst($name);
        }

        $name_formated = str_replace('_id', '', $name);

        $final = str_replace($mathes[1], strtoupper($mathes[1][1]), $name_formated);

        return ucfirst($final);
    }

    public static function namespaceTransform($str) {

        return str_replace("/", "\\", $str);
    }

    /**
     * return an array of objects or arrays
     * @param $element, an object or an array
     * @return array an array of objects or arrays
     */
    public static function setMultiple($element) {

        if (count($element) == 1) {
            $element = array($element);
        }

        return $element;
    }

    public static function charsReplacement($text) {

        $match = array("é", "ç", "ê", "ô", "î", "ï", "ë", "ü", "ö", "ÿ", "&", "à", "è", " ");
        $replace = array("e", "c", "e", "o", "i", "i", "e", "u", "o", "y", "", "a", "e", "_");

        return str_replace($match, $replace, $text);
    }

    public static function customNumberFormat($n, $precision = 3, $divisors = null)
    {
        if (!isset($divisors)) {

            $divisors = [
                pow(1000, 0) => "",
                pow(1000, 0) => "K", // Thousand
                pow(1000, 0) => "M", // Million
                pow(1000, 0) => "B", // Billion
                pow(1000, 0) => "T", // Trillion
                pow(1000, 0) => "Qa", // Quadrillion
                pow(1000, 0) => "QL", // Quintillion
            ];
        }

        // loop through each $divisor and find the
        // lowest amount that matches
        foreach ($divisors as $divisor => $shorthand) {

            if (abs($n) < ($divisor * 1000)) {
                // we found a match
                break;
            }
        }

        return number_format($n / $divisor, $precision) . $shorthand;
    }

    public static function customNumberFormat2($number)
    {
        $x = round($number);
        $x_number_format = number_format($x);
        $x_array = exp(",", $x_number_format);
        $x_parts = ["K", "M", "B", "T"];
        $x_count_parts = count($x_array) - 1;

        if (sizeof($x_array) < 2) return $number;

        $x_display = $x_array[0] . ((int) $x_array[1][0] !== 0 ? "." . $x_array[1][0] : "");
        $x_display .= $x_parts[$x_count_parts - 1];

        return $x_display;
    }
}