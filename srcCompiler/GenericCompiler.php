<?php

namespace Francerz\SqlBuilder\Compiler;

use Francerz\SqlBuilder\Components\Column;
use Francerz\SqlBuilder\Components\Join;
use Francerz\SqlBuilder\Components\JoinTypes;
use Francerz\SqlBuilder\Components\SqlValue;
use Francerz\SqlBuilder\Components\Table;
use Francerz\SqlBuilder\Expressions\BooleanResultInterface;
use Francerz\SqlBuilder\Expressions\ComparableComponentInterface;
use Francerz\SqlBuilder\Expressions\Comparison\BetweenExpression;
use Francerz\SqlBuilder\Expressions\Comparison\ComparisonOperationInterface;
use Francerz\SqlBuilder\Expressions\Comparison\InExpression;
use Francerz\SqlBuilder\Expressions\Comparison\LikeExpression;
use Francerz\SqlBuilder\Expressions\Comparison\NullExpression;
use Francerz\SqlBuilder\Expressions\Comparison\RegexpExpression;
use Francerz\SqlBuilder\Expressions\Comparison\RelationalExpression;
use Francerz\SqlBuilder\Expressions\Comparison\RelationalOperators;
use Francerz\SqlBuilder\Expressions\Logical\ConditionList;
use Francerz\SqlBuilder\Expressions\Logical\LogicConnectors;
use Francerz\SqlBuilder\InsertQuery;
use Francerz\SqlBuilder\QueryInterface;
use Francerz\SqlBuilder\SelectQuery;

class GenericCompiler
{
    private $values;

    public function compile(QueryInterface $query) : ?CompiledQuery
    {
        $this->clearValues();
        if ($query instanceof SelectQuery) {
            $sql = $this->compileSelect($query);
            $values = $this->getValues();
            return new CompiledQuery($sql, $values);
        }
        if ($query instanceof InsertQuery) {
            $sql = $this->compileInsert($query);
            $values = $this->getValues();
            return new CompiledQuery($sql, $values);
        }
        return null;
    }

    protected function clearValues()
    {
        $this->values = [];
    }

    protected function getValues()
    {
        return $this->values;
    }

    protected function addValue($value) : string
    {
        $key = 'v' . (count($this->values) + 1);
        $this->values[$key] = $value;
        return $key;
    }

    protected function compileSelect(SelectQuery $select) : string
    {
        $query = 'SELECT ';
        // COLUMNS
        $query.= $this->compileColumns($select->getAllColumns());
        // FROM
        $from = $select->getFrom();
        if (isset($from)) {
            $query.= ' FROM '. $this->compileTable($from->getTable());
        }
        // JOINS
        foreach ($select->getJoins() as $join) {
            $query.= $this->compileJoin($join);
        }
        // WHERE
        $query.= $this->compileConditionList($select->where(), ' WHERE ');
        // GROUP BY
        // HAVING
        $query.= $this->compileConditionList($select->having(), ' HAVING ');
        // ORDER BY
        return $query;
    }

    protected function compileInsert(InsertQuery $insert) : string
    {
        $query = 'INSERT INTO ';
        $query.= $this->compileTable($insert->getTable(), false);
        $columns = $insert->getColumns();
        $query.= '(' . join(',', $columns) . ') ';
        $values = $insert->getValues();
        if ($values instanceof SelectQuery) {
            $query.= $this->compileSelect($values);
            return $query;
        }
        if (!is_array($values)) {
            return $query;
        }
        $query.= 'VALUES ';
        $rows = [];
        foreach($values as $val) {
            $row = [];
            foreach ($columns as $col) {
                $row[] = isset($val[$col]) ? ':'.$this->addValue($val[$col]) : 'NULL';
            }
            $rows[] = implode(',', $row);
        }
        $query.= '('.implode('),(', $rows).')';
        return $query;
    }

    protected function compileColumns(array $columns)
    {
        if (empty($columns)) {
            return '*';
        }
        $cols = [];
        foreach ($columns as $c) {
            $cols[] = $this->compileColumn($c);
        }
        return join(', ', $cols);
    }

    protected function compileJoin(Join $join)
    {
        $output = $this->compileJoinType($join->getJoinType());
        $output.= $this->compileTable($join->getTableReference()->getTable());
        $output.= $this->compileConditionList($join->getOn(), ' ON ');
        return $output;
    }

    protected function compileJoinType($joinType) : string
    {
        switch ($joinType) {
            case JoinTypes::INNER_JOIN:
                return ' INNER JOIN ';
            case JoinTypes::LEFT_JOIN:
                return ' LEFT JOIN ';
            case JoinTypes::RIGHT_JOIN:
                return ' RIGHT JOIN ';
            case JoinTypes::LEFT_OUTER_JOIN:
                return ' LEFT OUTER JOIN ';
            case JoinTypes::RIGHT_OUTER_JOIN:
                return ' RIGHT OUTER JOIN ';
            case JoinTypes::FULL_OUTER_JOIN:
                return ' FULL OUTER JOIN ';
            case JoinTypes::CROSS_JOIN:
                return ', ';
        }
    }

    protected function compileTable(Table $table, bool $withAlias = true) : string
    {
        $alias = $table->getAlias();
        
        $output = $this->compileTableSource($table->getSource(), $table->getDatabase());
        if ($withAlias && isset($alias)) {
            $output .= ' AS '.$this->compileTableAlias($alias);
        }
        return $output;
    }

    protected function compileTableSource($source, ?string $database = null)
    {
        if ($source instanceof SelectQuery) {
            return '('.$this->compileSelect($source).')';
        }
        if (isset($database)) {
            return $this->compileTableDatabase($database).'.'.$this->compileTableName($source);
        }
        return $this->compileTableName($source);
    }

    protected function compileTableAlias(string $alias)
    {
        return $alias;
    }

    protected function compileTableName(string $name)
    {
        return $name;
    }

    protected function compileTableDatabase(string $database)
    {
        return $database;
    }

    protected function compileConditionList(ConditionList $conditions, string $prefix = '') : string
    {
        $output = '';
        if (count($conditions) > 0) {
            $output = $prefix;
            foreach ($conditions as $k => $item) {
                if ($k === 0) {
                    $output.= $this->compileBooleanExpression($item->getCondition());
                    continue;
                }
                $output.= $this->compileConnector($item->getConnector());
                $output.= $this->compileBooleanExpression($item->getCondition());
            }
        }
        return $output;
    }

    protected function compileConnector($connector)
    {
        switch ($connector) {
            case LogicConnectors::AND:
                return ' AND ';
            case LogicConnectors::OR:
                return ' OR ';
        }
    }

    protected function compileBooleanExpression(BooleanResultInterface $expr) : string
    {
        if ($expr instanceof ConditionList) {
            return '(' . $this->compileConditionList($expr) . ')';
        } elseif ($expr instanceof ComparisonOperationInterface) {
            return $this->compileComparison($expr);
        }
    }

    protected function compileComparison(ComparisonOperationInterface $expr) : string
    {
        if ($expr instanceof RelationalExpression) {
            return $this->compileRelationalExpression($expr);
        } elseif ($expr instanceof LikeExpression) {
            return $this->compileLikeExpression($expr);
        } elseif ($expr instanceof InExpression) {
            return $this->compileInExpression($expr);
        } elseif ($expr instanceof NullExpression) {
            return $this->compileNullExpression($expr);
        } elseif ($expr instanceof BetweenExpression) {
            return $this->compileBetweenExpression($expr);
        } elseif ($expr instanceof RegexpExpression) {
            return $this->compileRegexpExpression($expr);
        }
    }

    protected function compileRelationalExpression(RelationalExpression $expr) : string
    {
        $output = $this->compileComparable($expr->getOperand1());
        $output.= $this->compileRelationalOperator($expr->getOperator());
        $output.= $this->compileComparable($expr->getOperand2());
        return $output;
    }

    protected function compileRelationalOperator($operator)
    {
        switch ($operator) {
            case RelationalOperators::EQUALS:
                return ' = ';
            case RelationalOperators::NOT_EQUALS:
                return ' <> ';
            case RelationalOperators::LESS:
                return ' < ';
            case RelationalOperators::LESS_EQUALS:
                return ' <= ';
            case RelationalOperators::GREATER:
                return ' > ';
            case RelationalOperators::GREATER_EQUALS:
                return ' >= ';
        }
    }

    protected function compileLikeExpression(LikeExpression $expr) : string
    {
        $output = $this->compileComparable($expr->getOperand1());
        $output.= $expr->isNegated() ? ' NOT LIKE ' : ' LIKE ';
        $output.= $this->compileComparable($expr->getOperand2());
        return $output;
    }

    protected function compileInExpression(InExpression $expr) : string
    {
        $output = $this->compileComparable($expr->getOperand1());
        $output.= $expr->isNegated() ? ' NOT IN ' : ' IN ';
        return '';
    }

    protected function compileNullExpression(NullExpression $expr) : string
    {
        $output = $this->compileComparable($expr->getOperand());
        $output.= $expr->isNegated() ? ' IS NOT NULL' : ' IS NULL';
        return $output;
    }

    protected function compileBetweenExpression(BetweenExpression $expr) : string
    {
        $output = $this->compileComparable($expr->getOperand1());
        $output.= $expr->isNegated() ? ' NOT BETWEEN ' : ' BETWEEN ';
        $output.= $this->compileComparable($expr->getOperand2());
        $output.= ' AND ';
        $output.= $this->compileComparable($expr->getOperand3());
        return $output;
    }
    
    protected function compileRegexpExpression(RegexpExpression $expr) : string
    {
        $output = $this->compileComparable($expr->getOperand1());
        $output.= $expr->isNegated() ? ' NOT REGEXP ' : ' REGEXP ';
        $output.= $this->compileComparable($expr->getOperand2());
        return $output;
    }

    protected function compileComparable(ComparableComponentInterface $comp) : string
    {
        if ($comp instanceof Column) {
            return $this->compileColumn($comp);
        }
        return $this->compileValue($comp);
    }

    protected function compileColumn(Column $column) : string
    {
        $alias = $column->getAlias();
        $output = $this->compileColumnSource($column->getColumn(), $column->getTable());
        if (isset($alias)) {
            $output.= ' AS '.$this->compileColumnAlias($column->getAlias());
        }
        return $output;
    }

    protected function compileColumnSource($source, ?string $table)
    {
        if ($source instanceof SelectQuery) {
            return '('.$this->compileSelect($source).')';
        }
        $output = isset($table) ? $this->compileColumnTable($table).'.' : '';
        $output.= $this->compileColumnName($source);
        return $output;
    }

    protected function compileColumnName(string $name)
    {
        return $name;
    }

    protected function compileColumnTable(string $table)
    {
        return $table;
    }

    protected function compileColumnAlias(string $alias)
    {
        return $alias;
    }

    protected function compileValue(SqlValue $value) : string
    {
        return ':'.$this->addValue($value->getValue());
    }
}