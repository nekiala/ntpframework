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


        $name_formated = str_replace('_id', '', $name);

        $final = str_replace($mathes[1], strtoupper($mathes[1][1]), $name_formated);

        if (preg_match('`[a-z]+(_[a-z]{1})(.+)$`i', $final)) {

            return self::doRevert($final);
        }

        return ucfirst($final);
    }

    public static function doReplacements($tablename) {

        $matches = range('A', 'Z');

        foreach ($matches as $match) {
            $tablename = str_replace($match, '_' . $match, $tablename);
        }

        return strtolower(trim($tablename, '_'));
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
}