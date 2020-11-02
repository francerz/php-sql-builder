<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\Components\Column;
use Francerz\SqlBuilder\Components\SqlRaw;
use Francerz\SqlBuilder\Components\SqlValue;
use Francerz\SqlBuilder\Components\SqlValueArray;
use Francerz\SqlBuilder\Components\Table;

abstract class Query
{
    public static function column($name)
    {
        return Column::fromString($name);
    }
    public static function value($value)
    {
        return new SqlValue($value);
    }
    public static function array(array $array)
    {
        return new SqlValueArray($array);
    }
    public static function raw($content)
    {
        return new SqlRaw($content);
    }
    public static function selectFrom($table, ?array $columns = null)
    {
        return new SelectQuery(Table::fromExpression($table), $columns);
    }
    public static function insertInto($table, $values = null)
    {
        return new InsertQuery(Table::fromExpression($table), $values);
    }
    public static function update($table, $data = null, array $matching = [], array $columns = [])
    {
        $query = new UpdateQuery($table);
        if (empty($data)) {
            return $query;
        }
        if (is_object($data)) {
            $data = (array)$data;
        }
        foreach ($data as $k => $v) {
            if (in_array($k, $matching)) {
                $query->where()->equals($k, $v);
                continue;
            }
            if (!empty($columns) && in_array($k, $columns)) {
                $query->set($k, $v);
                continue;
            }
            $query->set($k, $v);
        }

        return $query;
    }
}