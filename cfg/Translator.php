<?php
/**
 * Created by PhpStorm.
 * User: Kiala
 * Date: 13/07/2015
 * Time: 14:17
 */

namespace cfg;


use cfg\app\Application;

class Translator {
    private static $translator_file = null;


    public function __construct() {

        if (is_null(self::$translator_file)) {

            $json = file_get_contents(Application::$system_files->getTranslationFile());

            self::$translator_file = json_decode($json, 1);
        }
    }

    public function translate($code, $locale) {
        if (!isset(self::$translator_file[$code])) {

            Application::$request_log->setMessage("Impossible to find the key " . $code)->notify();

            return 1;
        }

        $block = self::$translator_file[$code];

        if (!isset($block[$locale])) {

            Application::$request_log->setMessage("Impossible to find the locale " . $locale)->notify();
            return 1;
        }

        return $block[$locale];
    }
}