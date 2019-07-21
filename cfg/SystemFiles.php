<?php
/**
 * Created by PhpStorm.
 * User: Kiala
 * Date: 24/06/2015
 * Time: 16:43
 */

namespace cfg;

class SystemFiles extends SystemDirectories {

    private $application_file;
    private $log_file;
    private $modules_file;
    private $service_descriptor_file;
    private $database_string_file;
    private $sql_log_file;
    private $translation_file;

    public function __construct() {

        parent::__construct();

        if (!is_null(parent::$system_config)) {

            $this->application_file = $this->parse("application_file");
            $this->log_file = $this->parse("log_file");
            $this->modules_file = $this->parse("modules_file");
            $this->service_descriptor_file = $this->parse("service_descriptor_file");
            $this->database_string_file = $this->parse("database_string_file");
            $this->sql_log_file = $this->parse("sql_log_file");
            $this->translation_file = $this->parse("translation_file");
        }
    }

    public function parse($system_config_key) {

        static $out = array();

        if (!isset(self::$system_config["files"])) {

            throw new \OutOfRangeException("Cannot find the specified key");
        }

        $directories = self::$system_config["files"];

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
    public function getApplicationFile()
    {
        return $this->application_file;
    }

    /**
     * @param mixed $application_file
     */
    public function setApplicationFile($application_file)
    {
        $this->application_file = $application_file;
    }

    /**
     * @return mixed
     */
    public function getLogFile()
    {
        return $this->log_file;
    }

    /**
     * @param mixed $log_file
     */
    public function setLogFile($log_file)
    {
        $this->log_file = $log_file;
    }

    /**
     * @return mixed
     */
    public function getModulesFile()
    {
        return $this->modules_file;
    }

    /**
     * @param mixed $modules_file
     */
    public function setModulesFile($modules_file)
    {
        $this->modules_file = $modules_file;
    }

    /**
     * @return mixed
     */
    public function getServiceDescriptorFile()
    {
        return $this->service_descriptor_file;
    }

    /**
     * @param mixed $service_descriptor_file
     */
    public function setServiceDescriptorFile($service_descriptor_file)
    {
        $this->service_descriptor_file = $service_descriptor_file;
    }

    public static function getSystemFileKey($system_file_key) {


    }

    /**
     * @return string
     */
    public function getDatabaseStringFile()
    {
        return $this->database_string_file;
    }

    /**
     * @param string $database_string_file
     */
    public function setDatabaseStringFile($database_string_file)
    {
        $this->database_string_file = $database_string_file;
    }

    /**
     * @return string
     */
    public function getSqlLogFile()
    {
        return $this->sql_log_file;
    }

    /**
     * @param string $sql_log_file
     */
    public function setSqlLogFile($sql_log_file)
    {
        $this->sql_log_file = $sql_log_file;
    }

    /**
     * @return string
     */
    public function getTranslationFile()
    {
        return $this->translation_file;
    }

    /**
     * @param string $translation_file
     */
    public function setTranslationFile($translation_file)
    {
        $this->translation_file = $translation_file;
    }
}