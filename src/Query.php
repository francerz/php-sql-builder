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
use Francerz\SqlBuilder\Expressions\Logical\ConditionList;

abstract class Query
{
    #region Components
    public static function column($name)
    {
        return Column::fromString($name);
    }
    /**
     * @deprecated v0.3.14
     */
    public static function c($name) : Column
    {
        return call_user_func_array([Query::class, 'column'], func_get_args());
    }
    public static function value($value)
    {
        return new SqlValue($value);
    }
    /**
     * @deprecated v0.3.14
     */
    public static function v($name) : SqlValue
    {
        return call_user_func_array([Query::class, 'value'], func_get_args());
    }
    public static function array(array $array)
    {
        return new SqlValueArray($array);
    }
    /**
     * @deprecated v0.3.14
     */
    public static function a($name) : SqlValueArray
    {
        return call_user_func_array([Query::class, 'array'], func_get_args());
    }
    public static function raw($content)
    {
        return new SqlRaw($content);
    }
    /**
     * @deprecated v0.3.14
     */
    public static function r($content) : SqlRaw
    {
        return call_user_func_array([Query::class, 'raw'], func_get_args());
    }
    public static function func(string $name, ...$args)
    {
        return new SqlFunction($name, $args);
    }
    /**
     * @deprecated v0.3.14
     */
    public static function f(string $name, ...$args) : SqlFunction
    {
        return call_user_func_array([Query::class, 'func'], func_get_args());
    }
    #endregion

    #region Queries
    public static function selectFrom($table, ?array $columns = null, array $matches = [])
    {
        return SelectQuery::createSelect(Table::fromExpression($table), $columns, $matches);
    }
    public static function insertInto($table, $values = null, ?array $columns = null)
    {
        return new InsertQuery(Table::fromExpression($table), $values, $columns);
    }
    public static function update($table, $data = null, array $matching = [], array $columns = [])
    {
        return UpdateQuery::createUpdate($table, $data, $matching, $columns);
    }
    public static function upsert($table, $data = null, array $keys = [], ?array $columns = null)
    {
        return new UpsertQuery(Table::fromExpression($table), $data, $keys, $columns);
    }
    public static function deleteFrom($table, $filter = [])
    {
        return DeleteQuery::createFiltered($table, $filter);
    }
    #endregion

    #region Conditions
    public static function cond($mode = ComparisonModes::COLUMN_VALUE) : ConditionList
    {
        $cond = new ConditionList($mode);
        return $cond;
    }
    #endregion
}