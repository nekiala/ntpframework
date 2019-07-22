<?php

namespace cfg\app;

use cfg\app\observers\LogHandler;
use cfg\app\observers\WriteLog;
use cfg\app\services\Session;
use cfg\SystemFiles;

class Application
{
    const TOKEN_NAME = "token";
    const FORM_TOKEN_NAME = "_token";
    //termination for xhr rendered views
    //@nekiala, added on 20150909 1450
    const XHR_TERMINATION = "_xhr";

    const ROLE_FILE = 1;
    const ROLE_DB = 2;

    const APP_STAGE_DEVELOPMENT = 1;
    const APP_STAGE_PRODUCTION = 2;

    public $service;
    public $request;
    public $name;//application name

    private $environment;
    private $role_type;
    private $def_dir;

    // define if mysql, oracle, etc.
    private static $db_server;

    private static $stage;
    public static $system_files = null;
    public static $request_log = null;

    //added on 20151025 0750
    //application stage for handling errors
    const STAGE_DEVELOPMENT = 1;
    const STAGE_PRODUCTION = 2;

    private static $firewall_enabled = false;
    private static $user_must_be_enabled = false;

    public function __construct($do_it = false)
    {

        if ($do_it) {

            $this->request = new Request();
        }

        if (is_null(self::$system_files)) {

            self::$system_files = new SystemFiles();
            self::$request_log = new LogHandler();

            self::$request_log->attach(new WriteLog());
        }

        self::$stage = self::STAGE_DEVELOPMENT;
    }

    /**
     * @return boolean
     */
    public static function isFirewallEnabled()
    {
        return self::$firewall_enabled;
    }

    /**
     * @param boolean $firewall_enabled
     */
    public static function setFirewallEnabled($firewall_enabled)
    {
        self::$firewall_enabled = $firewall_enabled;
    }

    /**
     * @return boolean
     */
    public static function isUserMustBeEnabled()
    {
        return self::$user_must_be_enabled;
    }

    /**
     * @param boolean $user_must_be_enabled
     */
    public static function setUserMustBeEnabled($user_must_be_enabled)
    {
        self::$user_must_be_enabled = $user_must_be_enabled;
    }

    public function setEnvironment($environment)
    {
        $this->environment = $environment;
        return $this;
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    public function setRoleType($role_type = self::ROLE_FILE)
    {
        $this->role_type = $role_type;
        return $this;
    }

    public function getRoleType()
    {
        return $this->role_type;
    }

    public function roleTypeDB()
    {
        return self::ROLE_DB;
    }

    public function roleFile()
    {
        return self::ROLE_FILE;
    }

    public function setDefDir($environment, $dir)
    {
        // vo i windows
        if (strtolower(substr($environment, 0, 3)) === "win") {
            $this->def_dir = "/" . $dir . "/";
        } else {
            if ($dir == "/" || $dir == "") {
                $this->def_dir = "/";
            } else {
                $this->def_dir = "/" . $dir . "/";
            }
        }

        $session = $this->get("session");

        if (!$session->get("def_dir")) {
            //$json = file_get_contents(self::APP_FILE);
            $json = file_get_contents(self::$system_files->getApplicationFile());

            $file = json_decode($json, 1);

            $file['application_cfg']['root'] = $this->def_dir;

            $session->save("def_dir", $this->def_dir);

            //file_put_contents(self::APP_FILE, json_encode($file, JSON_UNESCAPED_SLASHES));
            file_put_contents(self::$system_files->getApplicationFile(), json_encode($file, JSON_UNESCAPED_SLASHES));
        }
        return $this;
    }

    public function getDefDir()
    {
        return $this->def_dir;
    }

    public function getSessionDefDir()
    {
        return $this->get('session')->get('def_dir');
    }

    /**
     * La méthode alimente la propriété $appname
     */
    public function getApplicationName()
    {

        try {
            $session = $this->get('session');
        } catch (\RuntimeException $e) {
            die($e->getMessage());
        }

        if (!$this->name = $session->get('app_name')) {
            $json = file_get_contents(self::$system_files->getApplicationFile());

            $file = json_decode($json, 1);
            $application_config = $file['application_cfg'];
            $this->name = $application_config['name'];

            $session->save('app_name', $this->name, 1);

        } else {
            $this->name = $session->get('app_name');
        }

        return $this->name;
    }

    public static function getLevelParam($parent, $muana)
    {
        //$json = file_get_contents(self::APP_FILE);
        $json = file_get_contents(self::$system_files->getApplicationFile());

        $file = json_decode($json, 1);

        $content = $file[$parent];

        return $content[$muana];
    }

    /**
     * Cette méthode permet de récupérer un service Web
     *
     * @param string $service le service qu'on souhaite récupérer
     * @return Object
     */
    public function get($service = "session")
    {

        $json = file_get_contents(self::$system_files->getServiceDescriptorFile());

        $file = json_decode($json, 1);

        $services = $file["services"];

        if (array_key_exists($service, $services)) {

            $class_name = $this->revertDir(self::$system_files->getServicesNamespace() . $services[$service]["class"]);

        } else {

            $class_name = $this->revertDir(self::$system_files->getServicesNamespace() . ucfirst($service));
        }

        if (class_exists($class_name)) {
            $this->service = new $class_name;
        } else {
            var_dump($services);
            die("Impossible de retrouver ce service ");
        }

        return $this->service;
    }

    private function revertDir($param)
    {

        return str_replace('/', '\\', $param);
    }

    /**
     * @return Session|object
     */
    public function getSession()
    {

        return $this->get("session");
    }

    public function run()
    {

        $this->getApplicationName();

        $this->request->execute();
    }

    /**
     * Vérifie la version de PHP utilisée et renvoie un boolean
     * @param $version
     * @return bool
     */
    public static function checkPHPVersion($version)
    {

        return PHP_VERSION < $version ? false : true;
    }

    /**
     * @return mixed
     */
    public static function getDbServer()
    {
        return self::$db_server;
    }

    /**
     * @param mixed $db_server
     */
    public static function setDbServer($db_server)
    {
        self::$db_server = $db_server;
    }

    /**
     * @return int
     */
    public static function getStage()
    {
        return self::$stage;
    }

    /**
     * @param int $stage
     */
    public static function setStage($stage)
    {
        self::$stage = $stage;
    }
}
