<?php

namespace cfg\app\db;
use cfg\app\Application;

/**
 * Utilise le design Singleton pour ne renvoyer qu'une seule instance de PDO
 *
 * @author Kiala
 */
class Instance {

    /**
     * @var \PDO|resource null
     */
    private static $_instance = null;
    private static $config_file = null;

    private static function getConnection() {
        if (is_null(self::$config_file)) {
            self::$config_file = json_decode(file_get_contents(Application::$system_files->getApplicationFile()), 1);
        }

        $default_driver = self::$config_file["database_config"]["default_config"];

        self::$_instance = Connection::getDriver($default_driver);
    }
    
    
    private function __construct() {
        if (is_null(self::$_instance)) {
            self::getConnection();
        }
    }
    
    public static function getInstance() {
        
        if (is_null(self::$_instance)) {
            
            /*self::$_instance = self::dbc();*/
            self::getConnection();
        }
        
        return self::$_instance;
    }
}
