<?php
/**
 * Created by PhpStorm.
 * User: Kiala
 * Date: 24/06/2015
 * Time: 15:56
 */

namespace cfg;


use cfg\app\interfaces\Parser;

class SystemDirectories implements Parser {

    private $config_directory;
    private $application_directory;
    private $views_directory;
    private $upload_directory;
    private $services_directory;
    private $controllers_directory;
    private $models_directory;
    private $database_string_directory;
    private $models_namespace;
    private $services_namespace;
    private $controllers_namespace;
    private $log_directory;
    private $managers_namespace;
    private $routing_directory;

    protected static $system_config = null;

    public function __construct($reset = 1, $filename = "cfg/system_config.json") {

        if (is_null($reset)) {
            self::$system_config = null;
        }

        if (is_null(self::$system_config)) {

            try {
                $file = file_get_contents($filename);
            } catch (\RuntimeException $e) {
                die("Cannot open " . $filename . " file");
            }

            self::$system_config = json_decode($file, 1);

            $this->config_directory = $this->parser("config_directory");
            $this->application_directory = $this->parser("application_directory");
            $this->views_directory = $this->parser("views_directory");
            $this->upload_directory = $this->parser("upload_directory");
            $this->services_directory = $this->parser("services_directory");
            $this->controllers_directory = $this->parser("controllers_directory");
            $this->models_directory = $this->parser("models_directory");
            $this->database_string_directory = $this->parser("database_string_directory");
            $this->models_namespace = $this->parser("models_namespace");
            $this->services_namespace = $this->parser("services_namespace");
            $this->controllers_namespace = $this->parser("controllers_namespace");
            $this->log_directory = $this->parser("log_directory");
            $this->managers_namespace = $this->parser("managers_namespace");
            $this->routing_directory = $this->parser("routing_directory");
        }

    }

    /**
     * @param $system_config_key
     * @return string
     */
    public function parser($system_config_key) {

        static $out = array();

        if (!isset(self::$system_config["directories"])) {

            throw new \OutOfRangeException("Cannot find the specified key");
        }

        $directories = self::$system_config["directories"];

        if (array_key_exists($system_config_key, $directories)) {

            if (strchr($directories[$system_config_key], "|")) {

                $dirs = explode("|", $directories[$system_config_key]);

                array_unshift($out, $dirs[count($dirs) - 1]);
                array_pop($dirs);

                if ($dirs > 1) {

                    for ($i = 0, $c = count($dirs); $i < $c; $i ++) {

                        array_unshift($out, $this->parser($dirs[$i]));
                    }
                }

            } else {
                array_unshift($out, $directories[$system_config_key]);
            }

        }

        $out_str = implode(DIRECTORY_SEPARATOR, $out);
        $out = array();
        return $out_str;
    }

    /**
     * @return mixed
     */
    public function getConfigDirectory()
    {
        return $this->config_directory;
    }

    /**
     * @param mixed $config_directory
     */
    public function setConfigDirectory($config_directory)
    {
        $this->config_directory = $config_directory;
    }

    /**
     * @return mixed
     */
    public function getApplicationDirectory()
    {
        return $this->application_directory;
    }

    /**
     * @param mixed $application_directory
     */
    public function setApplicationDirectory($application_directory)
    {
        $this->application_directory = $application_directory;
    }

    /**
     * @return mixed
     */
    public function getViewsDirectory()
    {
        return $this->views_directory;
    }

    /**
     * @param mixed $views_directory
     */
    public function setViewsDirectory($views_directory)
    {
        $this->views_directory = $views_directory;
    }

    /**
     * @return mixed
     */
    public function getUploadDirectory()
    {
        return $this->upload_directory;
    }

    /**
     * @param mixed $upload_directory
     */
    public function setUploadDirectory($upload_directory)
    {
        $this->upload_directory = $upload_directory;
    }

    /**
     * @return mixed
     */
    public function getServicesDirectory()
    {
        return $this->services_directory;
    }

    /**
     * @param mixed $services_directory
     */
    public function setServicesDirectory($services_directory)
    {
        $this->services_directory = $services_directory;
    }

    /**
     * @return mixed
     */
    public function getControllersDirectory()
    {
        return $this->controllers_directory;
    }

    /**
     * @param mixed $controllers_directory
     */
    public function setControllersDirectory($controllers_directory)
    {
        $this->controllers_directory = $controllers_directory;
    }

    /**
     * @return mixed
     */
    public function getModelsDirectory()
    {
        return $this->models_directory;
    }

    /**
     * @param mixed $models_directory
     */
    public function setModelsDirectory($models_directory)
    {
        $this->models_directory = $models_directory;
    }

    /**
     * @return mixed
     */
    public function getDatabaseStringDirectory()
    {
        return $this->database_string_directory;
    }

    /**
     * @param mixed $database_string_directory
     */
    public function setDatabaseStringDirectory($database_string_directory)
    {
        $this->database_string_directory = $database_string_directory;
    }

    /**
     * @return string
     */
    public function getModelsNamespace()
    {
        return $this->models_namespace;
    }

    /**
     * @param string $models_namespace
     */
    public function setModelsNamespace($models_namespace)
    {
        $this->models_namespace = $models_namespace;
    }

    /**
     * @return string
     */
    public function getServicesNamespace()
    {
        return $this->services_namespace;
    }

    /**
     * @param string $services_namespace
     */
    public function setServicesNamespace($services_namespace)
    {
        $this->services_namespace = $services_namespace;
    }

    /**
     * @return mixed
     */
    public function getControllersNamespace()
    {
        return $this->controllers_namespace;
    }

    /**
     * @param mixed $controllers_namespace
     */
    public function setControllersNamespace($controllers_namespace)
    {
        $this->controllers_namespace = $controllers_namespace;
    }

    /**
     * @return string
     */
    public function getLogDirectory()
    {
        return $this->log_directory;
    }

    /**
     * @param string $log_directory
     */
    public function setLogDirectory($log_directory)
    {
        $this->log_directory = $log_directory;
    }

    /**
     * @return mixed
     */
    public function getManagersNamespace()
    {
        return $this->managers_namespace;
    }

    /**
     * @param mixed $managers_namespace
     */
    public function setManagersNamespace($managers_namespace)
    {
        $this->managers_namespace = $managers_namespace;
    }

    /**
     * @return string
     */
    public function getRoutingDirectory()
    {
        return $this->routing_directory;
    }

    /**
     * @param string $routing_directory
     */
    public function setRoutingDirectory($routing_directory)
    {
        $this->routing_directory = $routing_directory;
    }
}