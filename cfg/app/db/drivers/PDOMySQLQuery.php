<?php
/**
 * Created by PhpStorm.
 * User: nekia_000
 * Date: 11/1/2015
 * Time: 7:17 AM
 */

namespace cfg\app\db\drivers;

require 'vendor/addendum/Annotations.php';
require 'cfg/app/db/Table.php';


use cfg\app\Application;
use cfg\app\db\Connector;
use cfg\app\db\DBServer;
use cfg\app\Reverter;

final class PDOMySQLQuery extends Connector
{
    const TYPE_ONE = 1;
    const TYPE_ALL = 2;
    const OPERATION_AND = 1;
    const OPERATION_OR = 2;

    /**
     * @var object
     */
    private $actualTable;

    protected $operations = array(self::OPERATION_AND => "AND", self::OPERATION_OR => "OR");

    /**
     * retourne le nom de la classe actuelle
     *
     * @return string
     */
    protected function getClassName()
    {

        $obj = new \ReflectionObject($this->actualTable);

        $name = substr($obj->getName(), strrpos($obj->getName(), "\\") + 1);

        $tableManager = explode('Manager', $name);

        return $tableManager[0];
    }

    /**
     * Retourne le nom de la table en lisant sur son annotation
     *
     * @param null $_class
     * @return string le nom de la table
     * @throws \ReflectionException
     */
    public function getTableName($_class = null)
    {

        if (!is_null($_class)) {
            $obj = new \ReflectionObject($_class);
        } else {
            $obj = new \ReflectionObject($this->actualTable);
        }

        $name = substr($obj->getName(), strrpos($obj->getName(), "\\") + 1);

        $tableManager = explode('Manager', $name);

        $cls = new \ReflectionAnnotatedClass(Application::$system_files->getModelsNamespace() . $tableManager[0]);

        if ($cls->hasAnnotation('Table')) {
            return $cls->getAnnotation('Table')->value;
        }

        return null;
    }

    // for code generation
    public function codeExist($code, $column = null) {

        $column_id = $this->getTableColumnId();
        $column_name = (is_null($column)) ? "code" : strip_tags(addslashes($column));

        $status = 0;

        $statement = $this->driver->prepare("SELECT {$column_id} FROM " . $this->getTableName() ." WHERE ({$column_name} = ?)");
        $statement->execute(array($code));
        if ($statement->rowCount()) $status = 1;

        $statement->closeCursor();

        return $status;
    }

    /**
     * Return table view
     *
     * @return string le nom de la view
     * @throws \ReflectionException
     */
    protected function getViewName()
    {

        $obj = new \ReflectionObject($this->actualTable);

        $name = substr($obj->getName(), strrpos($obj->getName(), "\\") + 1);

        $tableManager = explode('Manager', $name);

        $cls = new \ReflectionAnnotatedClass(Application::$system_files->getModelsNamespace() . $tableManager[0]);

        if ($cls->hasAnnotation('View')) {
            return $cls->getAnnotation('View')->value;
        }

        return null;
    }

    /**
     * @return \ReflectionAnnotatedClass
     * @throws \ReflectionException
     */
    private function getClass()
    {

        $obj = new \ReflectionObject($this->actualTable);

        $name = substr($obj->getName(), strrpos($obj->getName(), "\\") + 1);

        $tableManager = explode('Manager', $name);
        $class_name = Application::$system_files->getModelsNamespace() . $tableManager[0];

        $class = new \ReflectionAnnotatedClass($class_name);

        return $class;
    }

    /**
     * retourne le nom de la table, en vérifiant s'il s'agit d'une table
     * existante dans la base de données
     * @return string
     * @throws \Exception
     */
    protected function tableName()
    {
        $tables = $this->getTables();

        $table_name = Reverter::doReplacements($this->getTableName());

        //Application::$request_log->setMessage("Table name is: " . $table_name)->notify();

        foreach ($tables as $table) {
            if (in_array($table_name, $table)) {
                return $table_name;
            }
        }

        throw new \Exception("The table <b>{$table_name}</b> doesn't exist.");
    }

    /**
     * retrouve un resultat SQL à partir de l'argument $id
     *
     * @param int $id
     * @return object|null
     * @throws \Exception
     */
    public function find($id)
    {

        //récuperation du nom de la table
        $table = $this->tableName();

        //par défaut le nom de la colonne est id
        $column_id = $this->getTableColumnId();

        $stmt = $this->driver->prepare("SELECT * FROM {$table} WHERE ($column_id = ?)");
        $stmt->execute(array($id));

        if ($stmt->rowCount()) {

            return $this->populate($stmt);
        } else {

            return null;
        }
    }

    /**
     * Retourne l'identifiant de la table. Par défaut id
     * @return string identifiant de la table
     * @throws \ReflectionException
     */
    protected function getTableColumnId()
    {

        //recupération des propriétés de la classe qui appelle la méthode
        //$class = $this->getProperties("\models\\" . $this->getClassName());
        $class = $this->getProperties(Application::$system_files->getModelsNamespace() . $this->getClassName());

        //par défaut le nom de la colonne est null
        $column_id = null;

        //recherche dans les propriétés de la classe, l'annotation "Id"
        foreach ($class as $property) {

            //récupération des annotations pour chaque propriété
            //$annotated  = new \ReflectionAnnotatedProperty("\models\\" . $this->getClassName(), $property->name);
            $annotated = new \ReflectionAnnotatedProperty(Application::$system_files->getModelsNamespace() . $this->getClassName(), $property->name);

            //si l'annotation Column existe
            if ($annotated->hasAnnotation('Id') && $annotated->hasAnnotation('Column')) {
                //récupération de l'annotation
                $col = $annotated->getAnnotation('Column');

                $column_id = $col->name;
                break;
            }
        }

        return $column_id;
    }

    /**
     * Retourne l'identifiant de la table. Par défaut id
     * @return array identifiant de la table, ainsi que sa value
     * @throws \ReflectionException
     */
    protected function getTableColumnIdAndPropertyValue()
    {

        //recupération des propriétés de la classe qui appelle la méthode
        //$class = $this->getProperties("\models\\" . $this->getClassName());
        $class = $this->getProperties(Application::$system_files->getModelsNamespace() . $this->getClassName());

        //par défaut le nom de la colonne est null
        $column_id = null;
        $property_value = null;

        //recherche dans les propriétés de la classe, l'annotation "Id"
        foreach ($class as $property) {

            //récupération des annotations pour chaque propriété
            //$annotated  = new \ReflectionAnnotatedProperty("\models\\" . $this->getClassName(), $property->name);
            $annotated = new \ReflectionAnnotatedProperty(Application::$system_files->getModelsNamespace() . $this->getClassName(), $property->name);

            //si l'annotation Column existe
            if ($annotated->hasAnnotation('Id') && $annotated->hasAnnotation('Column')) {
                //récupération de l'annotation
                $col = $annotated->getAnnotation('Column');

                $column_id = $col->name;
                $property_value = $property->getName();
                break;
            }
        }

        return array($column_id, $property_value);
    }

    /**
     * retourne toutes les lignes de la table
     *
     * @return mixed
     * @throws \Exception
     */
    public function findAll()
    {

        $stmt = $this->driver->prepare("SELECT * FROM " . $this->tableName());
        $stmt->execute();

        if ($stmt->rowCount()) {

            return $this->populate($stmt);
        } else {

            return null;
        }
    }

    /**
     * @param array $where_clause
     * @param array|null $filter_clause
     * @param bool|false $limit
     * @return null|object
     * @throws \Exception
     */
    public function findFiltered(array $where_clause, array $filter_clause = null, $limit = false) {

        $clauses = $this->newClauseConstructor($where_clause, $this->operations[self::OPERATION_AND]);
        $filter = "";

        if (!is_null($filter_clause)) {
            $filters = "";

            foreach ($filter_clause as $key => $value) {
                $filters .= "{$key} {$value}, ";
            }

            $filter = trim($filters, ", ");
        }

        $sql = "SELECT * FROM " . $this->tableName();

        // if $where_clause is not an empty array
        if ($where_clause) $sql .=  " WHERE " . $clauses[0];

        // if filter is not null or not an empty array
        if ($filter) {
            $sql .= " ORDER BY {$filter}";
        }

        // is $limit is not set to false or is omitted
        if ($limit) $sql .= " LIMIT {$limit}";

        //Application::$request_log->setMessage($sql)->notify();

        $stmt = $this->driver->prepare($sql);
        $stmt->execute($clauses[1]);

        if ($stmt->rowCount()) {

            return $this->populate($stmt);

        } else {

            return null;
        }
    }

    /**
     * hydrate dynamiquement une classe, et tient
     * compte des relations entre les classes
     *
     * @param \PDOStatement $rows
     * @param null $class_name
     * @param bool $skip_relation
     * @return object
     */
    protected function populate(\PDOStatement $rows, $class_name = null, $skip_relation = false)
    {

        if (!is_null($class_name)) {

            $class = Application::$system_files->getModelsNamespace() . Reverter::doAllRevert($class_name);

        } else {

            $class = Application::$system_files->getModelsNamespace() . Reverter::doAllRevert($this->getClassName());
        }

        $properties = $this->getProperties('\\' . $class);

        $obj = $this->verifyPropertyRelational($rows, $class, $properties, $skip_relation);

        return $obj;
    }

    /**
     * vérifie les propriétés et automatiquement met à jour les relations
     * entre les classes
     *
     * @param \PDOStatement $rows
     * @param string $class
     * @param array $properties
     * @param bool $skip_relation
     * @return object|array
     */
    private function verifyPropertyRelational(\PDOStatement $rows, $class, array $properties, $skip_relation = false)
    {

        $objects = array();

        if ($rows->rowCount() == 1) {

            $row = $rows->fetch(\PDO::FETCH_ASSOC);

            return $this->hydrate($class, $row, $properties, $skip_relation);

        } else {

            while ($row = $rows->fetch(\PDO::FETCH_ASSOC)) {

                $objects[] = $this->hydrate($class, $row, $properties, $skip_relation);
            }

            return $objects;
        }
    }

    /**
     * @param $class
     * @param $row
     * @param $properties
     * @param bool $skip_relation
     * @return mixed
     * @throws \ReflectionException
     */
    public function hydrate($class, $row, $properties, $skip_relation = false)
    {

        $object = new $class;

        foreach ($properties as $property) {

            $name = $property->name;

            $method = "set" . Reverter::doRevert($name);

            $annotatedProperty = new \ReflectionAnnotatedProperty($class, $name);

            if ($annotatedProperty->hasAnnotation('Column')) {

                $key = $annotatedProperty->getAnnotation('Column');

                if ($annotatedProperty->hasAnnotation('Relation')) {

                    if ($skip_relation) {
                        continue;
                    }

                    $relation = $annotatedProperty->getAnnotation('Relation');

                    //nom de la fonction
                    if ($relation->multiple) {

                        $object_method = "findBy" . ucfirst($relation->column);

                    } else {

                        $object_method = "findOneBy" . ucfirst($relation->column);
                    }

                    //Application::$request_log->setMessage("Getting target for " . $relation->target)->notify();
                    $manager = $this->createManagerString($relation->target);

                    //echo $manager . '->' . $object_method . '(' . $row[$key->name] . ')<br />';

                    if (isset($row[$key->name])) {

                        $subClass = $this->getManager($manager)->$object_method($row[$key->name]);

                        // si $subclass est null
                        if ($subClass == null) {
                            // $subclass devient une instance vide
                            //Application::$request_log->setMessage("The subclass with method {$object_method} and argument {$row[$key->name]} is NULL")->notify();
                            $subClass = new $relation->target();
                        }

                        $object->$method($subClass);
                    }

                } else {

                    if (isset($row[$key->name])) {

                        $object->$method(DBServer::checkObjectUtf8Encode($row[$key->name]));
                    }
                }
            }
        }

        return $object;
    }

    /**
     * this method returns all the data that are null
     * initially added on 27 nov 2018 15:36, included here on 23 jul 2019 11:35 AM
     * @param string $column_name
     * @param int $limit
     * @return object
     * @throws \ReflectionException
     */
    public function getNullValue(string $column_name, int $limit)
    {
        $stmt = $this->driver->query("SELECT * FROM " . $this->getTableName() . " WHERE ({$column_name} IS NULL) LIMIT {$limit}");

        return $this->populate($stmt);
    }

    /**
     * count all the records in the table
     * @param null $table table name
     * @return int
     * @throws \ReflectionException
     */
    public function getCount($table = null) {


        if (is_null($table)) {
            $table_name = $this->getTableName();
        } else {
            $table_name = $table;
        }

        $count = 0;
        $stmt = $this->driver->prepare("SELECT COUNT(*) AS num FROM ". $table_name);
        $stmt->execute();
        $stmt->bindColumn(1, $count);
        $stmt->fetch(\PDO::FETCH_BOUND);
        $stmt->closeCursor();

        return $count;
    }

    public function getCountWithParameter($column, array $params, $table_name = null) {

        $clause = $this->newClauseConstructor($params, $this->operations[self::OPERATION_AND]);
        $records = 0;

        if ($table_name) {

            $table = $table_name;

        } else {

            try {
                $table = $this->tableName();

            } catch (\Exception $e) {

                die($e->getMessage());
            }
        }

        $req = $this->driver->prepare("SELECT COUNT({$column}) FROM {$table} WHERE {$clause[0]}");

        $req->execute($clause[1]);
        $req->bindColumn(1, $records);
        $req->fetch(\PDO::FETCH_BOUND);
        $req->closeCursor();
        return $records;
    }

    public function getLatest($num, $table = null, $skip_relation = false) {

        try {
            $column_id = $this->getTableColumnId();
        } catch (\ReflectionException $e) {

            die($e->getMessage());
        }

        try {
            $table_name = $this->getTableName();
        } catch (\ReflectionException $e) {

            die($e->getMessage());
        }
        $stmt = $this->driver->prepare("SELECT * FROM ". $table_name . " ORDER BY {$column_id} DESC LIMIT " .$num);
        $stmt->execute();

        return $this->populate($stmt, $table, $skip_relation);
    }

    /**
     * return the last day of a given date
     * @param $date
     * @return string
     */
    public function getLastDay($date): string
    {
        $last_day = null;
        $stmt = $this->driver->prepare("SELECT LAST_DAY(?) AS lastDay");

        $stmt->execute([$date]);
        $stmt->bindColumn(1, $last_day);
        $stmt->fetch(\PDO::FETCH_BOUND);
        $stmt->close();

        return $last_day;
    }

    /**
     * la méthode reçoit un argument du format \models\Class,
     * et renvoi le format ClassManager
     *
     * @param string $param
     * @return string
     */
    private function createManagerString($param)
    {

        $sep = explode(Application::$system_files->getModelsNamespace(), $param);

        $string = Reverter::doRevert($sep[1]);

        return $string;
    }

    /**
     * retourne une ou plusieurs lignes d'enregistrements à partir des paramètres
     * en argument.
     *
     * @param array $params une composition de clé => valeur
     * @param int $operation l'opération logique à effectuer : AND, OR, etc.
     * @return mixed
     * @throws \Exception
     */
    public function findWithClause(array $params, $operation = self::OPERATION_AND)
    {

        $clause = $this->newClauseConstructor($params, $this->operations[$operation]);

        $table = $this->tableName();

        $req = $this->driver->prepare("SELECT * FROM {$table} WHERE {$clause[0]}");

        $req->execute($clause[1]);

        if ($req->rowCount()) {

            return $this->populate($req);

        }

        return null;
    }

    /**
     * return number record in a table
     * @date 2015/08/21
     * @param array $params
     * @return int
     * @throws \Exception
     */
    public function getRecord(array $params) {

        $clause = $this->newClauseConstructor($params, $this->operations[self::OPERATION_AND]);
        $records = 0;

        $table = $this->tableName();

        $req = $this->driver->prepare("SELECT COUNT(*) FROM {$table} WHERE {$clause[0]}");
        $req->execute($clause[1]);
        $req->bindColumn(1, $records);
        $req->fetch(\PDO::FETCH_BOUND);
        $req->closeCursor();
        return $records;
    }

    /**
     * return sum record in a table
     * @date 2016/02/25
     * @param array $params
     * @param string $column
     * @return int
     * @throws \Exception
     */
    public function getSum($column, array $params) {

        $clause = $this->newClauseConstructor($params, $this->operations[self::OPERATION_AND]);
        $records = 0;

        $table = $this->tableName();

        $req = $this->driver->prepare("SELECT SUM({$column}) FROM {$table} WHERE {$clause[0]}");

        //Application::$request_log->setMessage($req->queryString. " " . serialize($clause[1]))->notify();

        $req->execute($clause[1]);
        $req->bindColumn(1, $records);
        $req->fetch(\PDO::FETCH_BOUND);
        $req->closeCursor();
        return $records;
    }

    public function getSumWithParameter($column, array $params) {

        $clause = $this->newClauseConstructor($params, $this->operations[self::OPERATION_AND]);
        $records = 0;

        try {
            $table = $this->tableName();

        } catch (\Exception $e) {

            die($e->getMessage());
        }

        $req = $this->driver->prepare("SELECT IFNULL(SUM({$column}), 0) AS a FROM {$table} WHERE {$clause[0]}");

        $req->execute($clause[1]);
        $req->bindColumn(1, $records);
        $req->fetch(\PDO::FETCH_BOUND);
        $req->closeCursor();
        return $records;
    }

    /**
     * return sum record in a table
     * @date 2016/02/25
     * @param array $params
     * @param string $column
     * @return int
     * @throws \Exception
     */
    public function getAverage($column, array $params) {

        $clause = $this->newClauseConstructor($params, $this->operations[self::OPERATION_AND]);
        $average = 0;

        $table = $this->tableName();

        $req = $this->driver->prepare("SELECT AVG ({$column}) FROM {$table} WHERE {$clause[0]}");

        //Application::$request_log->setMessage($req->queryString. " " . serialize($clause[1]))->notify();

        $req->execute($clause[1]);
        $req->bindColumn(1, $average);
        $req->fetch(\PDO::FETCH_BOUND);
        $req->closeCursor();
        return $average;
    }

    public function getAverageWithParameter($column, array $params, $table_name = null) {

        $clause = $this->newClauseConstructor($params, $this->operations[self::OPERATION_AND]);
        $average = 0;

        if ($table_name) {

            $table = $table_name;

        } else {

            try {
                $table = $this->tableName();

            } catch (\Exception $e) {

                die($e->getMessage());
            }
        }

        $req = $this->driver->prepare("SELECT IFNULL(AVG({$column}), 0) AS a FROM {$table} WHERE {$clause[0]}");

        $req->execute($clause[1]);
        $req->bindColumn(1, $average);
        $req->fetch(\PDO::FETCH_BOUND);
        $req->closeCursor();
        return $average;
    }

    public function doTruncate($table_name = null)
    {
        if ($table_name) {

            $table = $table_name;

        } else {

            try {
                $table = $this->tableName();

            } catch (\Exception $e) {

                die($e->getMessage());
            }
        }

        $this->driver->query("TRUNCATE TABLE {$table}");
    }

    /**
     * Retourne la requête se basant sur l'argument $field de cette table
     *
     * @param string $field la colonne sur laquelle la requête se fera
     * @param string $argument l'argument de la colonne $field
     * @param int $operation
     * @return mixed la requête
     * @throws \Exception
     */
    public function getTableDescription($field, $argument, $operation = self::OPERATION_AND)
    {

        $table = $this->tableName();

        $req = $this->driver->prepare("DESCRIBE {$table}");
        $req->execute();

        while ($data = $req->fetch(\PDO::FETCH_ASSOC)) {

            if ($data['Field'] == $field) {

                $req->closeCursor();

                return $this->findWithClause(array($field => $argument), $operation);

            } elseif ($data['Field'] == $field . "_id") {

                $req->closeCursor();

                return $this->findWithClause(array($field . "_id" => $argument), $operation);

            } elseif ($data['Field'] == Reverter::doReplacements($field) . "_id") {

                $req->closeCursor();

                return $this->findWithClause(array(Reverter::doReplacements($field) . "_id" => $argument), $operation);
            }
        }

        $req->closeCursor();

        throw new \RuntimeException("La colonne {$field} n'existe pas.");
    }

    /**
     * retourne toutes les tables dans la base de données
     *
     * @return mixed
     */
    public function getTables()
    {

        if (!$this->getDBStructure()) {

            $tables = array();

            $req = $this->driver->prepare("SHOW TABLES");
            $req->execute();

            while ($table = $req->fetch(\PDO::FETCH_NUM)) {

                $tables[] = $table;
            }

            $req->closeCursor();

            $this->saveDBStructure($tables);

            return $tables;
        } else {
            return $this->getDBStructure();
        }
    }

    /**
     * retourne les propriétés de l'objet en paramètres
     * @param $object
     * @return \ReflectionProperty[]
     * @throws \ReflectionException
     */
    private function getProperties($object)
    {
        if ($object[0] != "\\") {
            $object = "\\" . $object;
        }

        $name = Reverter::doAllRevert($object);

        $class = new \ReflectionClass($name);

        return $class->getProperties();
    }

    /**
     * persist une entity dans la table specified
     * @param $object
     * @throws \ReflectionException
     */
    public function persist(&$object)
    {

        if (is_object($object)) {

            $class = $this->getClass();

            if ($class->hasAnnotation('Table')) {

                //return $class->getAnnotation('Table')->value;

                //$class_name = "\models\\" . $this->getClassName();
                $class_name = Application::$system_files->getModelsNamespace() . $this->getClassName();

                //$properties = $this->getProperties("\models\\" . $this->getClassName());
                $properties = $this->getProperties($class_name);

                $values = $this->readProperties($object, $properties, $class_name);

                $column_id = Reverter::doAllRevert($this->getTableColumnId());

                $method = "set" . $column_id;

                //if ($class->hasMethod('setId')) {
                if ($class->hasMethod($method)) {

                    //$object->setId($this->ntpInsert($values, $class->getAnnotation('Table')->value));
                    $object->$method($this->ntpInsert($values, $class->getAnnotation('Table')->value));

                } else {

                    $this->ntpInsert($values, $class->getAnnotation('Table')->value);
                }
            }
        }
    }

    public function update(&$object)
    {

        if (is_object($object)) {

            try {
                $class = $this->getClass();
            } catch (\ReflectionException $e) {

                die($e->getMessage());
            }

            if ($class->hasAnnotation('Table')) {

                $class_name = Application::$system_files->getModelsNamespace() . $this->getClassName();

                try {
                    $properties = $this->getProperties($class_name);
                } catch (\ReflectionException $e) {
                    die($e->getMessage());
                }

                $values = $this->readPropertiesForUpdate($object, $properties, $class_name);

                try {
                    $column_id = Reverter::doAllRevert($this->getTableColumnId());
                } catch (\ReflectionException $e) {
                    die($e->getMessage());
                }

                $method = "get" . $column_id;


                //$this->ntpUpdate($values, $class->getAnnotation('Table')->value, $object->getId());
                $this->ntpUpdate($values, $class->getAnnotation('Table')->value, $object->$method());
            }
        }
    }

    public function remove(&$object)
    {

        if (is_object($object)) {

            try {
                $class = $this->getClass();
            } catch (\ReflectionException $e) {
                die($e->getMessage());
            }

            if ($class->hasAnnotation('Table')) {

                try {
                    $column_detail = $this->getTableColumnIdAndPropertyValue();
                } catch (\ReflectionException $e) {
                    die($e->getMessage());
                }

                if ($column_detail[1] != null) {

                    try {
                        $getter_method = $this->findCorrectMethod($object, $column_detail[1]);
                    } catch (\Exception $e) {
                        die($e->getMessage());
                    }
                    $column_detail[1] = $object->$getter_method();

                    $this->ntpSimpleRemove($column_detail, $class->getAnnotation('Table')->value);
                    return;
                }

                try {
                    $properties = $this->getProperties(Application::$system_files->getModelsNamespace() . $this->getClassName());
                } catch (\ReflectionException $e) {
                    die($e->getMessage());
                }

                $class_name = Application::$system_files->getModelsNamespace() . $this->getClassName();

                $values = $this->readPropertiesForRemove($object, $properties, $class_name);

                //$this->ntpRemove($class->getAnnotation('Table')->value, $object->getId());
                $this->ntpRemove($values, $class->getAnnotation('Table')->value);
            }
        }

        return null;
    }

    /**
     * Removes a block of data with a particular value
     * @internal $stmt statement
     * @param $argument string column name and value
     * @throws \Exception
     */
    public function doRemove($argument)
    {
        $stmt = $this->driver->prepare("DELETE FROM " . $this->tableName() . " WHERE {$argument['column']} = ?");

        $stmt->execute([$argument['value']]);
        $stmt->closeCursor();
    }

    /**
     * @param $params
     * @param $table
     */
    private function ntpRemove($params, $table)
    {

        $db = $this->driver;

        $question_mark = "";

        foreach ($params[0] as $composition) {

            $question_mark .= "$composition = ? AND ";
        }

        $qm = trim($question_mark, "AND ");

        Application::$request_log->setMessage("DELETE FROM {$table} WHERE {$qm}")->notify();
        Application::$request_log->setMessage(implode(", ", $params[1]))->notify();

        $req = $db->prepare("DELETE FROM {$table} WHERE {$qm}");
        $req->execute($params[1]);
        $req->closeCursor();

        return;
    }

    private function ntpSimpleRemove($params, $table) {

        $stmt = $this->driver->prepare("DELETE FROM {$table} WHERE " . $params[0] . " = ?");

        //Application::$request_log->setMessage($stmt->queryString . " " . $params[1] . " se " . serialize($params))->notify();

        $stmt->execute(array($params[1]));
        $stmt->closeCursor();

        return;
    }

    private function ntpUpdate($params, $table, $column_value)
    {

        $db = $this->driver;

        $question_mark = "";

        $column_id = $this->getTableColumnId();

        foreach ($params[0] as $composition) {

            $question_mark .= "$composition = ?, ";
        }

        $qm = trim($question_mark, ", ");

        $params[1][] = $column_value;

        /*Application::$request_log->setMessage("UPDATE {$table} SET {$qm} WHERE {$column_id} = ?")->notify();
        Application::$request_log->setMessage(implode(', ', $params[1]))->notify();*/

        $req = $db->prepare("UPDATE {$table} SET {$qm} WHERE {$column_id} = ?");
        $req->execute($params[1]);
        $req->closeCursor();

        return;
    }

    /**
     * @param $params
     * @param $table
     * @return mixed
     */
    private function ntpInsert($params, $table)
    {

        $db = $this->driver;

        $table_column = implode(", ", $params[0]);
        $question_mark = "";

        for ($i = 0; $i < count($params[0]); $i++) {
            $question_mark .= "?, ";
        }

        /*foreach ($params[0] as $composition) {

            $question_mark .= "?, ";
        }*/

        $qm = trim($question_mark, ", ");

        /*Application::$request_log->setMessage("INSERT INTO {$table} ({$table_column}) VALUES({$qm})")->notify();
        Application::$request_log->setMessage(implode(", ", $params[1]))->notify();*/

        $req = $db->prepare("INSERT INTO {$table} ({$table_column}) VALUES({$qm})");
        if (!$req->execute($params[1])) {
            return false;
        }
        $req->closeCursor();

        return $db->lastInsertId();
    }

    private function verifyPropertyForUpdate($class, $object, $properties)
    {

        $values = array();

        foreach ($properties as $property) {

            try {

                $value = $this->findMethod($object, $property);

            } catch (\Exception $e) {

                die($e->getMessage());
            }

            if (is_object($value)) {

                try {
                    $annotation = new \ReflectionAnnotatedProperty($class, $property->name);
                } catch (\ReflectionException $e) {

                    die($e->getMessage());
                }

                if ($annotation->hasAnnotation('Relation')) {

                    $relation = $annotation->getAnnotation('Relation');

                    $subMethod = "get" . Reverter::doRevert($relation->column);

                    $value = DBServer::checkObjectUtf8Decode($value->$subMethod());
                }
            }

            $values[] = DBServer::checkObjectUtf8Decode($value);
        }

        return $values;
    }

    private function verifyProperty($class, $object, $properties)
    {

        $values = array();

        foreach ($properties as $property) {

            try {

                $value = $this->findMethod($object, $property);

            } catch (\Exception $e) {

                die($e->getMessage());
            }

            if (is_object($value)) {

                try {
                    $annotation = new \ReflectionAnnotatedProperty($class, $property->name);
                } catch (\ReflectionException $e) {

                    die($e->getMessage());
                }

                if ($annotation->hasAnnotation('Relation')) {

                    $relation = $annotation->getAnnotation('Relation');

                    $subMethod = "get" . Reverter::doRevert($relation->column);

                    $value = DBServer::checkObjectUtf8Decode($value->$subMethod());

                    if ($relation->nullable == true) {

                        if (!$value) {
                            $value = null;
                        }
                    }
                }
            }

            $values[] = DBServer::checkObjectUtf8Decode($value);
        }

        return $values;
    }

    /**
     * Lit les propriétés d'une classe et renvoi la formule SQL
     *
     * @param object $object
     * @param object $properties
     * @param $class_name
     * @return array
     * @throws \ReflectionException
     */
    private function readProperties($object, $properties, $class_name)
    {

        $property_tab = array();
        $final_properties = array();

        foreach ($properties as $property) {

            $reflection_annotated = new \ReflectionAnnotatedProperty($class_name, $property->name);

            if ($reflection_annotated->hasAnnotation('Column')) {

                $annotation = $reflection_annotated->getAnnotation('Column');

                // si la colonne contient la propriété skip, passer
                if ($annotation->skip == true) {
                    continue;
                }

                /**
                 * la contrainte SQL sur les relations qui autorisent des valeurs nulles
                 * peut poser des problèmes lors de l'insertion à la base de données
                 *
                 * je vérifie si la colonne actuelle contient l'annotation Relation,
                 * et que cette relation supporte par défaut la valeur null
                 *
                 * si tel est le cas, je vérifie si la valeur de cette colonne est nulle
                 * si elle l'est, je saute l'itération avec continue
                 */
                if ($reflection_annotated->hasAnnotation("Relation")) {

                    $relation = $reflection_annotated->getAnnotation("Relation");

                    if ($relation->nullable == true) {

                        // relation class
                        $related_object = "get" . Reverter::doRevert($annotation->name);

                        // getter of that relation class
                        $getter = "get" . Reverter::doRevert($relation->column);

                        // getting object from relation class
                        $newObject = $object->$related_object();

                        if (is_null($newObject)) continue;

                        // if value is null or doesn't exist, continue
                        if (!$newObject->$getter()) {

                            continue;
                        }
                    }
                }

                $final_properties[] = $annotation->name;

                $property_tab[] = new \ReflectionAnnotatedProperty(Application::$system_files->getModelsNamespace() . $this->getClassName(), $property->name);
            }
        }

        return array($final_properties, $this->verifyProperty(Application::$system_files->getModelsNamespace() . $this->getClassName(), $object, $property_tab));
    }

    /**
     * @param $object object
     * @param $property object propriety
     * @return string
     * @throws \Exception
     */
    private function findMethod($object, $property)
    {

        $method = "get" . Reverter::doRevert($property->name);
        $bool_method = "is" . Reverter::doRevert($property->name);

        if (method_exists($object, $method)) {

            $value = $object->$method();

        } elseif (method_exists($object, $bool_method)) {

            $value = $object->$bool_method();

        } else {

            throw new \Exception("La méthode recherchée {$method}, {$bool_method} n'existe pas dans la classe (" . $this->getClassName() . ")");
        }

        return $value;
    }

    /**
     * retourne la method ou la bool_method si elle exist dans cet object
     * @param $object
     * @param $property
     * @return string
     * @throws \Exception
     */
    private function findCorrectMethod($object, $property)
    {

        $method = "get" . Reverter::doRevert($property);
        $bool_method = "is" . Reverter::doRevert($property);

        if (method_exists($object, $method)) {

            $value = $method;

        } elseif (method_exists($object, $bool_method)) {

            $value = $bool_method;

        } else {

            throw new \Exception("La méthode recherchée {$method}, {$bool_method} n'existe pas dans la classe (" . $this->getClassName() . ")");
        }

        return $value;
    }

    /**
     * this method get the latest code registered to the database
     * and returns it for a new code generation, 27 nov 2018 12:53
     * @param string $last_code
     * @return string|null
     * @throws \ReflectionException
     * @version 1.0
     * @author nekiala
     */
    public function getLastCode(string $last_code = "AAA")
    {
        $stmt = $this->driver->query("SELECT code FROM " . $this->getTableName() . " WHERE (code IS NOT NULL) ORDER BY code DESC LIMIT 1");

        if (!$stmt->rowCount()) {

            $stmt->closeCursor();

            return $last_code;
        }

        $code = null;

        $stmt->bindColumn(1, $code);
        $stmt->fetch(\PDO::FETCH_BOUND);
        $stmt->closeCursor();

        return $code;
    }

    /**
     * @param $object object actual object
     * @param $properties array object properties
     * @param $class_name string class name
     * @return array return values
     * @throws \ReflectionException
     */
    private function readPropertiesForUpdate($object, $properties, $class_name)
    {

        $annotated_property_names = array();
        $annotated_column_names = array();

        foreach ($properties as $property) {

            $reflection_annotated = new \ReflectionAnnotatedProperty($class_name, $property->name);

            try {
                $method = $this->findCorrectMethod($object, $property->name);
            } catch (\Exception $e) {

                die($e->getMessage());
            }

            if ($reflection_annotated->hasAnnotation('Column')) {

                $annotation = $reflection_annotated->getAnnotation('Column');

                if ($reflection_annotated->hasAnnotation("Id")) {
                    continue;
                }

                // si la valeur est vide ou null, on la classe pas
                if (!is_object($object->$method()) && !$object->$method() && $object->$method() != 0) {
                    continue;
                }

                /**
                 * la contrainte SQL sur les relations qui autorisent des valeurs nulles
                 * peut poser des problèmes lors de l'insertion à la base de données
                 *
                 * je vérifie si la colonne actuelle contient l'annotation Relation,
                 * et que cette relation supporte par défaut la valeur null
                 *
                 * si tel est le cas, je vérifie si la valeur de cette colonne est nulle
                 * si elle l'est, je saute l'itération avec continue
                 */
                if ($reflection_annotated->hasAnnotation("Relation")) {

                    $relation = $reflection_annotated->getAnnotation("Relation");

                    if ($relation->nullable == true) {

                        // relation class
                        $related_object = "get" . Reverter::doRevert($annotation->name);

                        // getter of that relation class
                        $getter = "get" . Reverter::doRevert($relation->column);

                        // getting object from relation class
                        $newObject = $object->$related_object();

                        if (is_null($newObject)) continue;

                        // if value is null or doesn't exist, continue
                        if (!$newObject->$getter()) {

                            continue;
                        }
                    }
                }

                //putting column name on the array
                $annotated_column_names[] = $annotation->name;

                //$property_tab[] = new \ReflectionAnnotatedProperty('\models\\' . $this->getClassName(), $property->name);
                $annotated_property_names[] = new \ReflectionAnnotatedProperty(Application::$system_files->getModelsNamespace() . $this->getClassName(), $property->name);
            }
        }

        //return array($final_properties, $this->verifyPropertyForUpdate('\models\\' . $this->getClassName(), $object, $property_tab));
        return array($annotated_column_names, $this->verifyPropertyForUpdate(Application::$system_files->getModelsNamespace() . $this->getClassName(), $object, $annotated_property_names));
    }

    private function readPropertiesForRemove($object, $properties, $class_name)
    {

        $property_tab = array();
        $final_properties = array();
        $has_default_id = false;

        foreach ($properties as $property) {

            $reflection_annotated = new \ReflectionAnnotatedProperty($class_name, $property->name);

            if ($reflection_annotated->hasAnnotation("Id")) {

                $has_default_id = true;
            }

            try {

                //$value = $this->findMethod($object, $property);
                $method = $this->findCorrectMethod($object, $property->name);
                //$bool_method = "is" . Reverter::doRevert($property->name);
            } catch (\Exception $e) {

                die($e->getMessage());
            }

            if ($reflection_annotated->hasAnnotation('Column')) {

                $annotation = $reflection_annotated->getAnnotation('Column');

                // si la valeur est vide ou null, on la classe pas
                if (!is_object($object->$method()) && !$object->$method() && $object->$method() != 0) {
                    continue;
                }

                // specific for delete actions
                //20150929 1749
                // editer 0n 20151001 0928
                if (!is_object($object->$method()) && $object->$method() == "") continue;

                /**
                 * la contrainte SQL sur les relations qui autorisent des valeurs nulles
                 * peut poser des problèmes lors de l'insertion à la base de données
                 *
                 * je vérifie si la colonne actuelle contient l'annotation Relation,
                 * et que cette relation supporte par défaut la valeur null
                 *
                 * si tel est le cas, je vérifie si la valeur de cette colonne est nulle
                 * si elle l'est, je saute l'itération avec continue
                 */
                if ($reflection_annotated->hasAnnotation("Relation")) {

                    $relation = $reflection_annotated->getAnnotation("Relation");

                    if ($relation->nullable == true) {

                        // relation class
                        $related_object = "get" . Reverter::doRevert($annotation->name);

                        // getter of that relation class
                        $getter = "get" . Reverter::doRevert($relation->column);

                        // getting object from relation class
                        $newObject = $object->$related_object();

                        if (is_null($newObject)) continue;

                        // if value is null or doesn't exist, continue
                        if (!$newObject->$getter()) {

                            continue;
                        }
                    }
                }

                $final_properties[] = $annotation->name;

                try {
                    $property_tab[] = new \ReflectionAnnotatedProperty(Application::$system_files->getModelsNamespace() . $this->getClassName(), $property->name);
                } catch (\ReflectionException $e) {

                    die($e->getMessage());
                }
            }
        }

        return array($final_properties, $this->verifyPropertyForUpdate(Application::$system_files->getModelsNamespace() . $this->getClassName(), $object, $property_tab), $has_default_id);
    }

    /**
     * @return object
     */
    public function getActualTable()
    {
        return $this->actualTable;
    }

    /**
     * @param object $actualTable
     */
    public function setActualTable($actualTable)
    {
        $this->actualTable = $actualTable;
    }
}