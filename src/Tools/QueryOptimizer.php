<?php

namespace Francerz\SqlBuilder\Tools;

use Francerz\SqlBuilder\Components\Column;
use Francerz\SqlBuilder\Components\Table;
use Francerz\SqlBuilder\Expressions\BooleanResultInterface;
use Francerz\SqlBuilder\Expressions\Logical\LogicConnectors;
use Francerz\SqlBuilder\Expressions\OneOperandInterface;
use Francerz\SqlBuilder\Expressions\TwoOperandsInterface;
use Francerz\SqlBuilder\SelectQuery;

abstract class QueryOptimizer
{
    public static function optimizeSelect(SelectQuery $query): SelectQuery
    {
        static::applySubqueryFilter($query);
        return $query;
    }

    private static function applySubqueryFilter(SelectQuery $query): SelectQuery
    {
        foreach ($query->where() as $cond) {
            if ($cond->getConnector() != LogicConnectors::AND) {
                continue;
            }
            $cnd = $cond->getCondition();

            if ($cnd instanceof OneOperandInterface) {
                static::applyOneOperandFilter($cnd, $query);
            } elseif ($cnd instanceof TwoOperandsInterface) {
                static::applyTwoOperandFilter($cnd, $query);
            }
        }
        $table = $query->getFrom()->getTable();
        static::optimizeTable($table);
        foreach ($query->getJoins() as $join) {
            $table = $join->getTableReference()->getTable();
            static::optimizeTable($table);
        }
        return $query;
    }

    private static function optimizeTable(Table $table)
    {
        $source = $table->getSource();
        if ($source instanceof SelectQuery) {
            $source = static::applySubqueryFilter($source);
            $table->setSource($source);
        }
    }

    /**
     * @param [type] $operand
     * @param SelectQuery $query
     * @param SelectQuery|null $subquery
     * @return void
     */
    private static function getSubqueryColumn($operand, SelectQuery $query, ?SelectQuery &$subquery = null)
    {
        if (!$operand instanceof Column) {
            return;
        }
        $subquery = static::getOperandSubquery($operand, $query);
        if (is_null($subquery)) {
            return;
        }

        $colname = $operand->getColumn();
        $column = $subquery->getColumn($colname);
        if (is_null($column)) {
            $column = new Column($colname);
        }
        return $column;
    }

    /**
     * @param BooleanResultInterface&OneOperandInterface $cond
     * @param SelectQuery $query
     * @return void
     */
    private static function applyOneOperandFilter($cond, SelectQuery $query)
    {
        $operand = $cond->getOperand();
        $column = static::getSubqueryColumn($operand, $query, $subquery);
        if (is_null($column) || is_null($subquery)) {
            return;
        }

        $cond = clone $cond;
        $cond->setOperand($column);
        $subquery->where()($cond);
    }

    /**
     * @param BooleanResultInterface&TwoOperandsInterface $cond
     * @param SelectQuery $query
     * @return void
     */
    private static function applyTwoOperandFilter($cond, SelectQuery $query)
    {
        $op1 = $cond->getOperand1();
        $op2 = $cond->getOperand2();
        if ($op1 instanceof Column && $op2 instanceof Column) {
            return;
        }

        $cond = clone $cond;
        if ($op1 instanceof Column) {
            $column = static::getSubqueryColumn($op1, $query, $subquery);
            if (is_null($column) || is_null($subquery)) {
                return;
            }
            $cond->setOperand1($column);
            $subquery->where()($cond);
        } elseif ($op2 instanceof Column) {
            $column = static::getSubqueryColumn($op2, $query, $subquery);
            if (is_null($column) || is_null($subquery)) {
                return;
            }
            $cond->setOperand2($column);
            $subquery->where()($cond);
        }
    }

    /**
     * @param Column $operand
     * @param SelectQuery $query
     * @return SelectQuery|null
     */
    private static function getOperandSubquery(Column $operand, SelectQuery $query): ?SelectQuery
    {

        $alias = $operand->getTable();
        if (is_null($alias)) {
            return null;
        }
        $tr = $query->getTableReference($alias);
        if (is_null($tr)) {
            return null;
        }

        $table = $tr->getTable();
        $source = $table->getSource();
        if ($source instanceof SelectQuery) {
            return $source;
        } elseif (is_string($source)) {
            return null;
        }

        return null;
    }
}
