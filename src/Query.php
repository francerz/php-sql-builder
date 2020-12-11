<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\Components\Column;
use Francerz\SqlBuilder\Components\SqlFunction;
use Francerz\SqlBuilder\Components\SqlRaw;
use Francerz\SqlBuilder\Components\SqlValue;
use Francerz\SqlBuilder\Components\SqlValueArray;
use Francerz\SqlBuilder\Components\Table;
use Francerz\SqlBuilder\Expressions\ComparableComponentInterface;
use Francerz\SqlBuilder\Expressions\Comparison\ComparisonModes;
use Francerz\SqlBuilder\Expressions\Comparison\NullExpression;
use Francerz\SqlBuilder\Expressions\Comparison\RelationalExpression;
use Francerz\SqlBuilder\Expressions\Comparison\RelationalOperators;

abstract class Query
{
    #region Components
    public static function column($name)
    {
        return Column::fromString($name);
    }
    public static function c($name) : Column
    {
        return call_user_func_array([Query::class, 'column'], func_get_args());
    }
    public static function value($value)
    {
        return new SqlValue($value);
    }
    public static function v($name) : Column
    {
        return call_user_func_array([Query::class, 'value'], func_get_args());
    }
    public static function array(array $array)
    {
        return new SqlValueArray($array);
    }
    public static function a($name) : Column
    {
        return call_user_func_array([Query::class, 'array'], func_get_args());
    }
    public static function raw($content)
    {
        return new SqlRaw($content);
    }
    public static function r($content)
    {
        return call_user_func_array([Query::class, 'raw'], func_get_args());
    }
    public static function func(string $name, ...$args)
    {
        return new SqlFunction($name, $args);
    }
    public static function f(string $name, ...$args)
    {
        return call_user_func_array([Query::class, 'func'], func_get_args());
    }
    #endregion

    #region Queries
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
    #endregion

    #region Operations
    private static function coarseModeFirst($operand, $mode)
    {
        if ($operand instanceof ComparableComponentInterface) {
            return $operand;
        }
        if ($mode & 0x10 > 0) {
            return static::value($operand);
        }
        return static::column($operand);
    }
    private static function coarseModeSecond($operand, $mode)
    {
        if ($operand instanceof ComparableComponentInterface) {
            return $operand;
        }
        if ($mode & 0x01 > 0) {
            return static::value($operand);
        }
        return static::column($operand);
    }

    public static function isNull($expr, $negated = false)
    {
        $expr = static::coarseModeFirst($expr, ComparisonModes::COLUMN_COLUMN);
        return new NullExpression($expr, $negated);
    }

    public static function isNotNull($expr) : NullExpression
    {
        return call_user_func_array([Query::class, 'isNull'], func_get_args());;
    }

    public static function equals($operand1, $operand2, $mode = ComparisonModes::COLUMN_VALUE)
    {
        $operand1 = static::coarseModeFirst($operand1, $mode);
        $operand2 = static::coarseModeSecond($operand2, $mode);
        return new RelationalExpression($operand1, $operand2, RelationalOperators::EQUALS);
    }
    public static function eq($operand1, $operand2, $mode = ComparisonModes::COLUMN_VALUE) : RelationalExpression
    {
        return call_user_func_array([Query::class, 'equals'], func_get_args());
    }
    #endregion
}