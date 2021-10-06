<?php

namespace Francerz\SqlBuilder;

use Countable;
use Francerz\SqlBuilder\Components\Table;

class InsertQuery implements QueryInterface, Countable
{
    private $table = null;
    /**
     * @var object[]|array[]|SelectQuery
     */
    private $values = [];
    /**
     * @var string[]
     */
    private $columns = [];

    public function __construct($table = null, $values = [], ?array $columns = null)
    {
        if (isset($table)) {
            $this->setTable($table);
        }
        if (!empty($values)) {
            $this->setValues($values, $columns);
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

    public function setValues($values, ?array $columns = null)
    {
        if (is_array($values) && count(array_filter(array_keys($values), 'is_int')) > 0) {
            foreach ($values as $row) {
                $this->setValues($row, $columns);
            }
            return;
        }
        if ($values instanceof SelectQuery) {
            $this->values = $values;
            return;
        }
        if (is_object($values)) {
            $values = (array)$values;
        }
        if (is_array($columns)) {
            $columns = array_combine($columns, $columns);
            $values = array_intersect_key($values, $columns);
        }
        $this->values[] = $values;
        $this->columns = array_unique(array_merge($this->columns, array_keys($values)));
    }

    public function getTable(): Table
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
