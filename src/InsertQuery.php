<?php

namespace Francerz\SqlBuilder;

use Countable;
use Francerz\PowerData\Arrays;
use Francerz\SqlBuilder\Components\Table;
use Francerz\SqlBuilder\Helpers\ModelHelper;

class InsertQuery implements QueryInterface, Countable
{
    private $connection = null;
    private $table = null;
    /** @var object[]|array[]|SelectQuery */
    private $values = [];
    /** @var string[] */
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

    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->values);
    }

    private static function normalizeColumns(?array $columns = null)
    {
        if (is_null($columns)) {
            return null;
        }

        $newColumns = [];
        foreach ($columns as $k => $v) {
            $k = is_int($k) ? $v : $k;
            $newColumns[$k] = $v;
        }
        return $newColumns;
    }

    public function setValues($values, ?array $columns = null)
    {
        $columns = static::normalizeColumns($columns);
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
        $values = ModelHelper::dataAsArray($values);
        if (is_array($columns)) {
            $values = array_intersect_key($values, $columns);
            $values = Arrays::replaceKeys($values, $columns);
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
