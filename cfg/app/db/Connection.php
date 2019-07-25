<?php
/**
 * Created by PhpStorm.
 * User: nekia_000
 * Date: 10/25/2015
 * Time: 8:07 AM
 */

namespace cfg\app\db;


use cfg\app\Application;
use cfg\app\observers\LogHandler;

final class Connection
{
    private static $driver;
    public static $active_driver;
    /*
     * PDO compatible driver section constants keys definition
     */
    const PDO_MYSQL_KEY = "pdo_mysql";
    const PDO_MARIADB_KEY = "pdo_maria_db";
    const PDO_ORACLE_OCI_KEY = "pdo_oracle_oci";
    const PDO_PGSQL_KEY = "pdo_pgsql";
    const PDO_SQL_SERVER_KEY = "pdo_mssql";
    const PDO_IBM_DB2_KEY = "pdo_ibm";

    /*
     * native compatible driver section constants keys definition
     */
    const MYSQL_KEY = "mysql";
    const MARIADB_KEY = "maria_db";
    const ORACLE_OCI8_KEY = "oracle_oci8";
    const MICROSOFT_MSSQL_KEY = "mssql";
    const PGSQL_KEY = "pgsgl";
    const IBM_DB2 = "ibm_db2";


    private static $cfg_file = null;
    private static $config_section = null;

    public final function __construct($driver_key = null) {

        if (is_null(self::$cfg_file)) {
            self::$cfg_file = json_decode(file_get_contents(Application::$system_files->getApplicationFile()), 1);
            self::$active_driver = $driver_key;
        }

        $this->getConfigSection();
        $this->connect($driver_key);
    }

    public function connect($driver_key = null) {
        if (is_null($driver_key)) {
            die ("Specify driver key");
        }

        $driver = null;

        switch ($driver_key) {
            case self::PDO_MYSQL_KEY:
                $driver = $this->PDOMySQL($driver_key);
                break;
            case self::PDO_MARIADB_KEY:
                $driver = $this->PDOMariaDB();
                break;
            case self::PDO_ORACLE_OCI_KEY:
                $driver = $this->PDOOci();
                break;
            case self::PDO_PGSQL_KEY:
                $driver = $this->PDOPgSQL();
                break;
            case self::PDO_SQL_SERVER_KEY:
                $driver = $this->PDOMsSQL();
                break;
            case self::PDO_IBM_DB2_KEY:
                $driver = $this->PDOIbmDb2();
                break;
            case self::MYSQL_KEY:
                $driver = $this->MySQL();
                break;
            case self::MARIADB_KEY:
                $driver = $this->MARIADB();
                break;
            case self::PGSQL_KEY:
                $driver = $this->PGSQL();
                break;
            case self::MICROSOFT_MSSQL_KEY:
                $driver = $this->MsSql();
                break;
            case self::ORACLE_OCI8_KEY:
                $driver = $this->OracleOci8();
                break;
        }

        if (!$driver) {
            die ("Cannot find driver.");
        }

        self::$driver =  $driver;
    }

    public final function PDOMySQL($key_name = self::PDO_MYSQL_KEY) {

        $driver = self::$config_section[$key_name];

        $dsn = $driver["com"] .  $driver["dbname"];
        $auth_section = $driver["auth"];
        $username = $auth_section["user"];
        $password = $auth_section["pwd"];

        return $this->PDOConnect($dsn, $username, $password);
    }

    public final function PDOMariaDB() {

        //Application::$request_log->setMessage("MARIA DB Driver Called")->notify();
        return $this->PDOMySQL(self::PDO_MARIADB_KEY);

    }

    public final function PDOPgSQL() {
        //Application::$request_log->setMessage("PDO_PGSQL_KEY Driver Called")->notify();
        return $this->PDOMySQL(self::PDO_PGSQL_KEY);
    }

    public final function PDOOci() {

    }

    public final function PDOIbmDb2() {

    }

    public final function PDOMsSQL() {

        $driver = self::$config_section[self::PDO_SQL_SERVER_KEY];

        $dsn = $driver["com"] .  $driver["database"];
        $auth_section = $driver["auth"];
        $username = $auth_section["user"];
        $password = $auth_section["pwd"];

        return $this->PDOConnect($dsn, $username, $password);
    }

    public final function OracleOci8() {
        $driver = self::$config_section[self::ORACLE_OCI8_KEY];

        $dsn = $driver["com"];
        $charset = $driver["charset"];

        $auth_section = $driver["auth"];

        $schema = $auth_section["schema"];
        $password = $auth_section["pwd"];

        $conn = oci_pconnect($schema, $password, $dsn, $charset);

        if (!$conn) {

            $message = oci_error();

            Application::$request_log->setType(LogHandler::TYPE_EXCEPTION)->setMessage("OCI8 MESSAGE: " . $message["message"])->notify();

            die();
        }
        return $conn;
    }

    public final function MySQL() {

        $driver = self::$config_section[self::MYSQL_KEY];

        $port = 3306;

        if (isset($driver["port"])) {

            $port = intval($driver["port"]);
        }

        $dsn = $driver["com"];

        $database = $driver["database"];

        $auth_section = $driver["auth"];
        $username = $auth_section["user"];
        $password = $auth_section["pwd"];

        $conn = @new \mysqli($dsn, $username, $password, $database, $port);

        if ($conn->connect_errno) {
            die(sprintf("Couldn't connect to MySQL (mysqli driver). <br>Error no: %d<br>Message: %s<br>", $conn->connect_errno, $conn->connect_error));
        }

        return $conn;
    }

    public final function MariaDB() {

        return $this->MySQL();
    }

    public final function PgSQL() {}

    public final function IbmDb2() {}

    public final function MsSql() {

    }

    private function getConfigSection() {

        if (is_null(self::$config_section)) {
            self::$config_section = self::$cfg_file["database_config"]["cfg"];
        }

        return self::$config_section;
    }

    private final function PDOConnect($dsn, $username, $password) {

        try {

            $driver = new \PDO($dsn, $username, $password);

            $driver->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        } catch (\PDOException $e) {

            Application::$request_log->setType(LogHandler::TYPE_EXCEPTION)->setMessage($e->getMessage())->notify();

            die ($e->getMessage());
        }

        return $driver;
    }


    public static function getDriver($default_driver) {

        new Connection($default_driver);

        return self::$driver;
    }

}