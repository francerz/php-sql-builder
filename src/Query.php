<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\Components\Column;
use Francerz\SqlBuilder\Components\SqlFunction;
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
    public static function func($name, $args)
    {
        return new SqlFunction($name, $args);
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
        return UpdateQuery::createUpdate($table, $data, $matching, $columns);
    }
    public static function deleteFrom($table, $filter = [])
    {
        return DeleteQuery::createFiltered($table, $filter);
    }
}