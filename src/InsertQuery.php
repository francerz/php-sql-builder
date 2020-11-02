<?php

namespace Francerz\SqlBuilder;

use Countable;
use Francerz\SqlBuilder\Components\Table;

class InsertQuery implements QueryInterface, Countable
{
    private $table = null;
    private $values = [];
    private $columns = [];

    public function __construct($table = null, $values = [])
    {
        if (isset($table)) {
            $this->setTable($table);
        }
        if (!empty($values)) {
            $this->setValues($values);
        }
    }

    public function setTable($table)
    {
        if (!$table instanceof Table) {
            $table = Table::fromExpression($table);
        }
        $this->table = $table;
    }

    public function count()
    {
        return count($this->values);
    }

    public function setValues($values, ?callable $objectParser = null)
    {
        if (is_array($values) && count(array_filter(array_keys($values), 'is_int')) > 0) {
            foreach ($values as $row) {
                $this->setValues($row, $objectParser);
            }
            return;
        }
        if ($values instanceof SelectQuery) {
            $this->values = $values;
            return;
        }
        if (is_object($values)) {
            if (isset($objectParser)) {
                $values = call_user_func($objectParser, $values);
            }
            $values = (array)$values;
        }
        $this->values[] = $values;
        $this->columns = array_merge($this->columns, array_keys($values));
    }

    public function getTable() : Table
    {
        return $this->table;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function setColumns($columns)
    {
        $this->columns = array_values(array_unique($columns));
    }

    public function getColumns()
    {
        return array_values(array_unique($this->columns));
    }
}