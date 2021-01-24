<?php

namespace Francerz\SqlBuilder;

class UpsertQuery extends InsertQuery
{
    private $keys = [];

    public function __construct($table = null, $values = [], ?array $columns = null, array $keys = [])
    {
        parent::__construct($table, $values, $columns);
        $this->keys = $keys;
    }

    public function getUpdateQuery() : UpdateQuery
    {
        return UpdateQuery::createUpdate($this->getTable(), $this->getValues(), $this->keys, $this->getColumns());
    }
}