<?php

namespace Francerz\SqlBuilder;

use Countable;
use Francerz\PowerData\Arrays;
use Francerz\SqlBuilder\Components\Table;
use Francerz\SqlBuilder\Helpers\ModelHelper;
use Iterator;

class UpsertQuery implements Iterator, Countable
{
    private $table = null;
    private $values = [];
    private $keys = [];
    private $columns = [];

    public function __construct($table = null, $values = null, array $keys = [], array $columns = [])
    {
        if (isset($table)) {
            $this->setTable($table);
        }
        if (isset($values)) {
            $cols = empty($columns) ? [] : array_merge($keys, $columns);
            $this->setValues($values, $cols);
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

    private static function normalizeColumns(array $columns = [])
    {
        if (empty($columns)) {
            return [];
        }

        $newColumns = [];
        foreach ($columns as $k => $v) {
            $k = is_int($k) ? $v : $k;
            $newColumns[$k] = $v;
        }
        return $newColumns;
    }

    public function setValues($values, array $columns = [])
    {
        $columns = static::normalizeColumns($columns);
        if (is_array($values) && count(array_filter(array_keys($values), 'is_int')) > 0) {
            foreach ($values as $row) {
                $this->setValues($row, $columns);
            }
            return;
        }
        $values = ModelHelper::dataAsArray($values);
        if (!empty($columns)) {
            $values = array_intersect_key($values, $columns);
            $values = Arrays::replaceKeys($values, $columns);
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

    public function getUpdateColumns()
    {
        return array_values(array_diff($this->columns, $this->keys));
    }

    public function count()
    {
        return count($this->values);
    }

    public function current()
    {
        return current($this->values);
    }

    public function next()
    {
        next($this->values);
    }

    public function rewind()
    {
        return reset($this->values);
    }

    public function key()
    {
        return key($this->values);
    }

    public function valid()
    {
        return key($this->values) !== null;
    }
}
