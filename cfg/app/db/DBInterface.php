<?php

namespace cfg\app\db;

interface DBInterface {
    
    public function find($id);
    
    public function findAll();
    
    public function findWithClause(array $params, $operation);

    public function getCount($table = null);

    public function findFiltered(array $where_clause, array $filter_clause = null, $limit = false);

    public function update(&$object);

    public function persist(&$object);

    public function remove($object);
}
