<?php
/**
 * Created by PhpStorm.
 * User: nekia_000
 * Date: 10/10/2015
 * Time: 9:52 PM
 */

namespace cfg\app\db;


use cfg\app\Application;

class DBServer
{
    const MYSQL = "MYSQL";
    const MARIADB = "MARIADB";
    const ORACLE = "ORACLE";

    public static function checkObjectUtf8Encode($value) {
        return (Application::getDbServer() == self::MARIADB) ? $value : utf8_encode($value);
    }

    public static function checkObjectUtf8Decode($value) {
        return (Application::getDbServer() == self::MARIADB) ? $value : utf8_decode($value);
    }
}