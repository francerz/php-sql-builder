<?php

namespace Francerz\SqlBuilder;

use Iterator;
use LogicException;
use Traversable;

class UpsertQuery extends InsertQuery
{
    private $keys = [];

    public function __construct($table = null, $value = null, array $keys = [], ?array $columns = null)
    {
        parent::__construct($table, $value, $columns);
        $this->keys = $keys;
    }

    public function getKeys() : array
    {
        return $this->keys;
    }

    public function getUpdateQuery() : UpdateQuery
    {
        $values = $this->getValues();
        if ($values instanceof SelectQuery) {
            throw new \Exception('Upsert with SelectQuery not yet supported.');
        }
        if (!is_array($values) && !$values instanceof Iterator) {
            throw new \Exception('Invalid values for upserting.');
        }
        if (count($values) !== 1) {
            throw new LogicException('Only can get UpdateQuery from single row');
        }
        $update = UpdateQuery::createUpdate($this->getTable(), $values[0], $this->keys, $this->getColumns());
        return $update;
    }
}