<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\Components\Column;
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
    public static function selectFrom($table, ?array $columns = null)
    {
        return new SelectQuery(Table::fromExpression($table), $columns);
    }
}