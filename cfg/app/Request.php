<?php

namespace cfg\app;

/**
 * Description of Request
 *
 * @author Kiala
 */
class Request {
    
    private static $server;
    public $router;
    public $session;


    public function getRouter() {
        return $this->router;
    }
    
    /**
     * retourne le type de requête (POST, GET, etc.)
     * 
     * @return string
     */
    public function getMethod() {
        return self::$server['REQUEST_METHOD'];
    }
    
    public function isMethod($method) {
        return self::$server['REQUEST_METHOD'] == $method;
    }
    
    /**
     * retourne l'URI
     * 
     * @return string
     */
    public function getURI() {
        return self::$server['REQUEST_URI'];
    }

    /**
     * Vérifie la présence d'une requête AJAX<br />
     * Ce script je l'ai trouvé dans le site http://davidwalsh.name/detect-ajax
     * @author davidwa
     * @link http://davidwalsh.name/detect-ajax
     * @return bool
     */
    public function isXHR() {

        return !empty(self::$server['HTTP_X_REQUESTED_WITH']) && strtolower(self::$server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    /**
     * Retourne le super global $_POST
     * 
     * @return $_POST
     */
    public function getPOST() {
        return $_POST;
    }

    public function getFileGlobal() {
        return isset($_FILES) ? $_FILES : false;
    }

    /**
     * renvoi le token. par défaut c'est le nom du token des formulaire,
     * définit dans la classe Application
     * @param string $key
     * @return bool
     */
    public function getToken($key = Application::FORM_TOKEN_NAME) {

        return $this->key($key);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function key($key) {
        
        $post = $this->getPOST();
        
        return isset($post[$key]) ? $post[$key] : false;
    }

    public function requestObj($key) {

        $post = $_REQUEST;

        return isset($post[$key]) ? $post[$key] : false;
    }
    
    public function execute() {
        
        $default_controller = Application::getLevelParam('app', 'default_module');
        $def_action = Application::getLevelParam('app', 'default_action');
        $default_dir = Application::getLevelParam('application_cfg', 'root');
        $this->router = new RouterV2($this->getURI(), Application::$system_files->getControllersNamespace(), $default_controller, $def_action, $default_dir);
    }
    
    public function getParams($param = '_') {
        
        $post = $this->getPOST();
        
        return $post[$param];
    }

    /**
     * @author nekiala
     *
     * sanitize and validate integer
     * @param $integer
     * @return int|bool
     */
    private function checkInteger($integer)
    {
        $value = filter_var($integer, FILTER_SANITIZE_NUMBER_INT);

        if (filter_var($value, FILTER_VALIDATE_INT)) {

            return $value;
        }

        return false;
    }

    /**
     * @author nekiala
     *
     * sanitize and validate float
     * @param $double
     * @return float|bool
     */
    private function checkDouble($double)
    {
        $value = filter_var($double, FILTER_SANITIZE_NUMBER_FLOAT);

        if (filter_var($value, FILTER_VALIDATE_FLOAT)) {

            return $value;
        }

        return false;
    }

    /**
     * @author nekiala
     *
     * sanitize and validate string
     * @param $string
     * @return string
     */
    private function checkString($string)
    {
        $value = filter_var($string, FILTER_SANITIZE_STRING);

        return $value;
    }

    /**
     * @author nekiala
     *
     * sanitize and validate email
     * @param $email
     * @return string|bool
     */
    private function checkEmail($email)
    {
        $value = filter_var($email, FILTER_SANITIZE_EMAIL);

        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {

            return $value;
        }

        return false;
    }

    public function getSession() {

    }

    public function __construct() {
        
        if (is_null(self::$server)) {
            self::$server = $_SERVER;
        }
    }
}
