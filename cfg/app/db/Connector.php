<?php

namespace cfg\app\db;

use cfg\app\Application;
use cfg\app\Reverter;

class Connector {

    /**
     * @var \PDO|\mysqli|resource
     */
    public $driver;
    public $log;

    //contain the database string file
    private static $db_string_file = null;
    /**
     *
     * @var object $manager
     */
    private $manager;
    
    private function getDBStructureFile() {
        
        return self::$db_string_file;
    }
    
    private function getDBStructureFileContent() {
        
        return $json = file_get_contents(self::$db_string_file);
    }

    /**
     * 
     * @param string $manager la classe à rechercher
     * @return DBInterface|object $manager le manager de la classe
     */
    public function getManager($manager) {

        $_manager = Reverter::namespaceTransform(Application::$system_files->getManagersNamespace()) . $manager . "Manager";

        if (class_exists($_manager)) {

            $this->manager = new $_manager;
            
        } else {

            die("Le Manager " . $manager . " n'existe pas.");
        }

        return $this->manager;
    }
    
    /**
     * cette fonction construit une chaine SQL à partir d'une clause
     * 
     * @param array $params paremètres pour la requête
     * @param string $_c clause (OR, AND)
     * @return string
     */
    public function clauseConstructor(array $params, $_c) {
        
        $keys = array_keys($params);
        $clause = "";
        $_clause = "";

        foreach($keys as $key) {
            $clause .= "({$key} = '" . addslashes($params[$key]) . "') {$_c} ";
            $_clause = trim($clause, " {$_c} ");
        }
        
        return $_clause;
    }

    /**
     * Return a prepared query
     * @param array $params arguments
     * @param $operation string operation to perform (and, or)
     * @return array keys and values
     */
    protected function newClauseConstructor(array $params, $operation) {

        $array_keys = array_keys($params);

        $keys = ""; $values = array();

        foreach ($array_keys as $key) {
            $keys .= "({$key} = ?) {$operation} ";
            $values[] = $params[$key];
        }

        return array(trim($keys, " {$operation}"), $values);
    }
    
    public function saveDBStructure(array $table_str) {

        $filename = $this->getDBStructureFile();
        
        file_put_contents($filename, json_encode($table_str, JSON_UNESCAPED_SLASHES));
    }
    
    public function getDBStructure() {
        
        $json = $this->getDBStructureFileContent();
        
        $file = json_decode($json, 1);
        
        return empty($file) ? false : $file;
    }

    public function removeDBStructure() {

        $filename = $this->getDBStructureFile();

        file_put_contents($filename, "");
    }

    public function __construct() {

        $this->driver = Instance::getInstance();
        $this->log = Application::$request_log;

        if (is_null(self::$db_string_file)) {

            self::$db_string_file = Application::$system_files->getDatabaseStringFile();
        }
    }

}