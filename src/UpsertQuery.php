<?php

namespace Francerz\SqlBuilder;

use Iterator;

class UpsertQuery extends InsertQuery
{
    private $keys = [];

    public function __construct($table = null, $value = null, array $keys = [], ?array $columns = null)
    {
        parent::__construct($table, $value, $columns);
        $this->keys = $keys;
    }

    public function getUpdateQuery() : array
    {
        $values = $this->getValues();
        if ($values instanceof SelectQuery) {
            throw new \Exception('Upsert with SelectQuery not yet supported.');
        }
        if (!is_array($values) && !$values instanceof Iterator) {
            throw new \Exception('Invalid values for upserting.');
        }
        $updates = [];
        foreach ($values as $row) {
            $updates[] = UpdateQuery::createUpdate($this->getTable(), $row, $this->keys, $this->getColumns());
        }
        return $updates;
    }
}