<?php
/**
 * Created by PhpStorm.
 * User: nekia_000
 * Date: 11/16/2015
 * Time: 11:20 PM
 */

namespace cfg\app\db;


use cfg\app\Application;
use cfg\app\db\drivers\PDOMSSQL;
use cfg\app\db\drivers\PDOMySQLQuery;
use cfg\app\db\drivers\SimpleMySQLQuery;
use cfg\app\db\drivers\SimpleOracleQuery;
use cfg\app\Reverter;

class NtpQueryManager implements DBInterface
{
    /**
     * @var object
     */
    protected $database;
    protected $driver;

    const OPERATION_AND = 1;
    const OPERATION_OR = 2;

    protected $operations = array(self::OPERATION_AND => "AND", self::OPERATION_OR => "OR");

    public function __construct() {

        switch (Connection::$active_driver) {
            case Connection::PDO_MARIADB_KEY:
            case Connection::PDO_MYSQL_KEY:
                $this->database = new PDOMySQLQuery();
                break;
            case Connection::ORACLE_OCI8_KEY:
                $this->database = new SimpleOracleQuery();
                break;
            case Connection::PDO_SQL_SERVER_KEY:
                $this->database = new PDOMSSQL();
                break;
            case Connection::MARIADB_KEY:
            case Connection::MYSQL_KEY:
                $this->database = new SimpleMySQLQuery();
                break;
        }

        if (gettype($this->database) != 'object') {

            die(sprintf("%s didn't found the correct driver", NtpQueryManager::class));
        }

        $this->driver = $this->database->driver;

        $this->database->setActualTable($this);

        return $this->database;
    }

    public function findAll()
    {
        return $this->database->findAll();
    }

    public function find($id)
    {
        return $this->database->find($id);
    }

    public function __call($method, $argument) {
        $matches = null;
        $all_matches = null;

        preg_match("`(find)(By)(.+)`", $method, $matches);
        preg_match("`(findOne)(By)(.+)`", $method, $all_matches);

        // more results
        if (isset($matches[3])) {

            $field = strtolower(Reverter::doReplacements($matches[3]));

            if ($argument[0] || $argument[0] == 0) {
                try {
                    return $this->database->getTableDescription($field, $argument[0]);
                } catch (\Exception $e) {

                    Application::$request_log->setMessage($e->getMessage())->notify();
                    die();
                }
            }

            // only one result
        } elseif (isset($all_matches[3])) {

            $field = strtolower($all_matches[3]);

            if ($argument[0]) {
                try {
                    return $this->database->getTableDescription($field, $argument[0]);
                } catch (\Exception $e) {

                    Application::$request_log->setMessage($e->getMessage())->notify();
                    die();
                }
            }
        }

        return null;
    }

    public function findWithClause(array $params, $operation)
    {
        // TODO: Implement findWithClause() method.
    }

    public function hydrate($class, $row, $properties)
    {
        // TODO: Implement hydrate() method.
    }

    public function codeExist($code, $column = null)
    {
        // TODO: Implement codeExist() method.
    }

    public function getCount($table = null)
    {
        // TODO: Implement getCount() method.
        return $this->database->getCount($table);
    }

    public function findFiltered(array $where_clause, array $filter_clause = null, $limit = false)
    {
        // TODO: Implement findFiltered() method.
        try {
            return $this->database->findFiltered($where_clause, $filter_clause, $limit);
        } catch (\Exception $e) {

            Application::$request_log->setMessage($e->getMessage())->notify();
            die();
        }
    }

    public function update(&$object)
    {
        // TODO: Implement update() method.
        return $this->database->update($object);
    }

    public function persist(&$object)
    {
        // TODO: Implement persist() method.
        return $this->database->persist($object);
    }

    public function remove($object)
    {
        // TODO: Implement remove() method.
        return $this->database->remove($object);
    }
}