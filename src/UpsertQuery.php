<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\Components\Table;
use Francerz\SqlBuilder\Helpers\ModelHelper;
use Iterator;
use LogicException;

class UpsertQuery implements Iterator
{
    private $table = null;
    private $keys = [];

    public function __construct($table = null, $values = null, array $keys = [], ?array $columns = null)
    {
        if (isset($table)) {
            $this->setTable($table);
        }
        if (isset($values)) {
            $this->setValues($values, $columns);
        }
        $this->keys = $keys;
    }

    public function setTable($table)
    {
        $this->table = Table::fromExpression($table);
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function setValues($values, ?array $columns = null)
    {
        if (is_array($values) && count(array_filter(array_keys($values), 'is_int')) > 0) {
            foreach ($values as $row) {
                $this->setValues($row, $columns);
            }
            return;
        }
        $values = ModelHelper::dataAsArray($values);
        if (is_array($columns)) {
            $columns = array_combine($columns, $columns);
            $values = array_intersect_key($values, $columns);
        }
        $this->values[] = $values;
        $this->columns = array_unique(array_merge($this->columns, array_keys($values)));
    }

    public function getValues()
    {
        return $this->values;
    }

    public function getKeys(): array
    {
        return $this->keys;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getUpdateQuery(): UpdateQuery
    {
        $values = $this->getValues();
        if ($values instanceof SelectQuery) {
            throw new \Exception('Upsert with SelectQuery not supported.');
        }
        if (!is_array($values) && !$values instanceof Iterator) {
            throw new \Exception('Invalid values for upserting.');
        }
        if (count($values) !== 1) {
            throw new LogicException('Only can get UpdateQuery from single row.');
        }
        $update = UpdateQuery::createUpdate($this->getTable(), $values[0], $this->keys, $this->getColumns());
        return $update;
    }

    public function current()
    {
        return current($this->rows);
    }

    public function next()
    {
        next($this->rows);
    }

    public function rewind()
    {
        return reset($this->rows);
    }

    public function key()
    {
        return key($this->rows);
    }

    public function valid()
    {
        return key($this->rows) !== null;
    }
}
