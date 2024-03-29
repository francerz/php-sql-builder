<?php

namespace Francerz\SqlBuilder\Driver;

use DateTimeInterface;
use Francerz\SqlBuilder\Compiles\CompiledDelete;
use Francerz\SqlBuilder\Compiles\CompiledInsert;
use Francerz\SqlBuilder\Compiles\CompiledProcedure;
use Francerz\SqlBuilder\Compiles\CompiledSelect;
use Francerz\SqlBuilder\Compiles\CompiledUpdate;
use Francerz\SqlBuilder\Components\Column;
use Francerz\SqlBuilder\Components\Join;
use Francerz\SqlBuilder\Components\JoinTypes;
use Francerz\SqlBuilder\Components\Set;
use Francerz\SqlBuilder\Components\SqlFunction;
use Francerz\SqlBuilder\Components\SqlRaw;
use Francerz\SqlBuilder\Components\SqlValue;
use Francerz\SqlBuilder\Components\SqlValueArray;
use Francerz\SqlBuilder\Components\Table;
use Francerz\SqlBuilder\DeleteQuery;
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
use Francerz\SqlBuilder\SelectQuery;
use Francerz\SqlBuilder\StoredProcedure;
use Francerz\SqlBuilder\Traits\LimitableInterface;
use Francerz\SqlBuilder\Traits\SortableInterface;
use Francerz\SqlBuilder\UpdateQuery;

class QueryCompiler implements QueryCompilerInterface
{
    private $values;

    protected function clearValues()
    {
        $this->values = [];
    }

    protected function getValues()
    {
        return $this->values;
    }

    protected function addValue($value): string
    {
        if ($value instanceof DateTimeInterface) {
            $value = $this->compileDatetime($value);
        }
        $key = 'v' . (count($this->values) + 1);
        $this->values[$key] = $value;
        return $key;
    }

    public function compileSelect(SelectQuery $select): CompiledSelect
    {
        $this->clearValues();
        return new CompiledSelect(
            $this->compileSelectString($select),
            $this->getValues()
        );
    }

    public function compileInsert(InsertQuery $insert): CompiledInsert
    {
        $this->clearValues();
        return new CompiledInsert(
            $this->compileInsertString($insert),
            $this->getValues()
        );
    }

    public function compileUpdate(UpdateQuery $query): CompiledUpdate
    {
        $this->clearValues();
        return new CompiledUpdate(
            $this->compileUpdateString($query),
            $this->getValues()
        );
    }

    public function compileDelete(DeleteQuery $query): CompiledDelete
    {
        $this->clearValues();
        return new CompiledDelete(
            $this->compileDeleteString($query),
            $this->getValues()
        );
    }

    public function compileProcedure(StoredProcedure $procedure): CompiledProcedure
    {
        $this->clearValues();
        $sql = 'CALL ';
        $sql .= $procedure->getName();
        $params = [];
        foreach ($procedure->getParams() as $p) {
            $params[] = ':' . $this->addValue($p);
        }
        $sql .= '(' . join(',', $params) . ')';
        $values = $this->getValues();
        return new CompiledProcedure($sql, $values);
    }

    protected function compileSelectString(SelectQuery $select): string
    {
        $query = 'SELECT ';
        // COLUMNS
        $query .= $this->compileColumns($select->getAllColumns());
        // FROM
        $from = $select->getFrom();
        if (isset($from)) {
            $query .= ' FROM ' . $this->compileTable($from->getTable());
        }
        // JOINS
        foreach ($select->getJoins() as $join) {
            $query .= $this->compileJoin($join);
        }
        // WHERE
        $query .= $this->compileConditionList($select->where(), ' WHERE ');
        // GROUP BY
        $query .= $this->compileGroupBy($select);
        // HAVING
        $query .= $this->compileConditionList($select->having(), ' HAVING ');
        // ORDER BY
        $query .= $this->compileOrderBy($select);
        // LIMIT
        $query .= $this->compileLimit($select);
        return $query;
    }

    protected function compileInsertString(InsertQuery $insert): string
    {
        $query = 'INSERT INTO ';
        $query .= $this->compileTable($insert->getTable(), false);
        $columns = $insert->getColumns();
        $query .= '(' . join(',', $columns) . ') ';
        $values = $insert->getValues();
        if ($values instanceof SelectQuery) {
            $query .= $this->compileSelect($values);
            return $query;
        }
        if (!is_array($values)) {
            return $query;
        }
        $query .= 'VALUES ';
        $rows = [];
        foreach ($values as $val) {
            $row = [];
            foreach ($columns as $col) {
                $row[] = isset($val[$col]) ? ':' . $this->addValue($val[$col]) : 'NULL';
            }
            $rows[] = implode(',', $row);
        }
        $query .= '(' . implode('),(', $rows) . ')';
        return $query;
    }

    protected function compileUpdateString(UpdateQuery $update): string
    {
        $query = 'UPDATE ';
        $query .= $this->compileTable($update->getTable());
        foreach ($update->getJoins() as $join) {
            $query .= $this->compileJoin($join);
        }
        $query .= $this->compileSets($update->getSets(), ' SET ');
        $query .= $this->compileConditionList($update->where(), ' WHERE ');
        $query .= $this->compileLimit($update);
        return $query;
    }

    protected function compileDeleteString(DeleteQuery $delete): string
    {
        $query = 'DELETE FROM ';
        $rowsIn = $delete->getRowsIn();
        if (count($rowsIn) > 0) {
            $query = 'DELETE ' . join(',', $rowsIn) . ' FROM ';
        }
        $query .= $this->compileTable($delete->getTable());
        foreach ($delete->getJoins() as $join) {
            $query .= $this->compileJoin($join);
        }
        $query .= $this->compileConditionList($delete->where(), ' WHERE ');
        $query .= $this->compileLimit($delete);
        return $query;
    }

    protected function compileSets(array $sets, ?string $prefix = null): string
    {
        $_sets = [];
        foreach ($sets as $set) {
            $_set = '';
            if ($set instanceof Set) {
                $_set = $this->compileColumn($set->getColumn());
                $_set .= ' = ';
                $_set .= $this->compileComparable($set->getValue());
            }
            $_sets[] = $_set;
        }
        $output = isset($prefix) ? $prefix : '';
        $output .= join(', ', $_sets);
        return $output;
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
        $output .= $this->compileTable($join->getTableReference()->getTable());
        $output .= $this->compileConditionList($join->getOn(), ' ON ');
        return $output;
    }

    /**
     * @param JoinTypes $joinType
     * @return string
     */
    protected function compileJoinType($joinType): string
    {
        $joinType = JoinTypes::coerce($joinType);
        switch ((string)$joinType) {
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

    protected function compileTable(Table $table, bool $withAlias = true): string
    {
        $alias = $table->getAlias();

        $output = $this->compileTableSource($table->getSource(), $table->getDatabase());
        if ($withAlias && isset($alias)) {
            $output .= ' AS ' . $this->compileTableAlias($alias);
        }
        return $output;
    }

    protected function compileTableSource($source, ?string $database = null)
    {
        if ($source instanceof SelectQuery) {
            return '(' . $this->compileSelectString($source) . ')';
        }
        if (isset($database)) {
            return $this->compileTableDatabase($database) . '.' . $this->compileTableName($source);
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

    protected function compileConditionList(ConditionList $conditions, string $prefix = ''): string
    {
        $output = '';
        if (count($conditions) > 0) {
            $output = $prefix;
            foreach ($conditions as $k => $item) {
                if ($k === 0) {
                    $output .= $this->compileBooleanExpression($item->getCondition());
                    continue;
                }
                $output .= $this->compileConnector($item->getConnector());
                $output .= $this->compileBooleanExpression($item->getCondition());
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

    protected function compileBooleanExpression(BooleanResultInterface $expr): string
    {
        if ($expr instanceof ConditionList) {
            return '(' . $this->compileConditionList($expr) . ')';
        } elseif ($expr instanceof ComparisonOperationInterface) {
            return $this->compileComparison($expr);
        }
    }

    protected function compileComparison(ComparisonOperationInterface $expr): string
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

    protected function compileRelationalExpression(RelationalExpression $expr): string
    {
        $output = $expr->isNegated() ? 'NOT ' : '';
        $output .= $this->compileComparable($expr->getOperand1());
        $output .= $this->compileRelationalOperator($expr->getOperator());
        $output .= $this->compileComparable($expr->getOperand2());
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

    protected function compileLikeExpression(LikeExpression $expr): string
    {
        $output = $this->compileComparable($expr->getOperand1());
        $output .= $expr->isNegated() ? ' NOT LIKE ' : ' LIKE ';
        $output .= $this->compileComparable($expr->getOperand2());
        return $output;
    }

    protected function compileInExpression(InExpression $expr): string
    {
        $output = $this->compileComparable($expr->getOperand1());
        $output .= $expr->isNegated() ? ' NOT IN ' : ' IN ';
        $output .= $this->compileComparable($expr->getOperand2());
        return $output;
    }

    protected function compileNullExpression(NullExpression $expr): string
    {
        $output = $this->compileComparable($expr->getOperand());
        $output .= $expr->isNegated() ? ' IS NOT NULL' : ' IS NULL';
        return $output;
    }

    protected function compileBetweenExpression(BetweenExpression $expr): string
    {
        $output = $this->compileComparable($expr->getOperand1());
        $output .= $expr->isNegated() ? ' NOT BETWEEN ' : ' BETWEEN ';
        $output .= $this->compileComparable($expr->getOperand2());
        $output .= ' AND ';
        $output .= $this->compileComparable($expr->getOperand3());
        return $output;
    }

    protected function compileRegexpExpression(RegexpExpression $expr): string
    {
        $output = $this->compileComparable($expr->getOperand1());
        $output .= $expr->isNegated() ? ' NOT REGEXP ' : ' REGEXP ';
        $output .= $this->compileComparable($expr->getOperand2());
        return $output;
    }

    protected function compileComparable(ComparableComponentInterface $comp): string
    {
        if ($comp instanceof Column) {
            return $this->compileColumn($comp);
        }
        if ($comp instanceof SqlFunction) {
            return $this->compileFunction($comp);
        }
        if ($comp instanceof BooleanResultInterface) {
            return $this->compileBooleanExpression($comp);
        }
        if ($comp instanceof SqlRaw) {
            return $comp->getContent();
        }
        return $this->compileValue($comp);
    }

    protected function compileColumn(Column $column): string
    {
        $alias = $column->getAlias();
        $output = $this->compileColumnSource($column->getColumn(), $column->getTable());
        if (isset($alias)) {
            $output .= ' AS ' . $this->compileColumnAlias($column->getAlias());
        }
        return $output;
    }

    protected function compileColumnSource($source, ?string $table)
    {
        if ($source instanceof SelectQuery) {
            return '(' . $this->compileSelect($source) . ')';
        }
        if ($source instanceof SqlFunction) {
            return $this->compileFunction($source);
        }
        if ($source instanceof ComparableComponentInterface) {
            return $this->compileComparable($source);
            // return '(' . $this->compileComparable($source) . ')';
        }
        $output = isset($table) ? $this->compileColumnTable($table) . '.' : '';
        if ($source instanceof SqlRaw) {
            $output .= $source->getContent();
        } elseif ($source === '*') {
            $output .= '*';
        } else {
            $output .= $this->compileColumnName($source);
        }
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

    protected function compileFunction(SqlFunction $function)
    {
        $output = $function->getName() . '(';
        $args = [];
        foreach ($function->getArgs() as $arg) {
            if ($arg instanceof ComparableComponentInterface) {
                $args[] = $this->compileComparable($arg);
                continue;
            }
            if ($arg instanceof BooleanResultInterface) {
                $args[] = $this->compileBooleanExpression($arg);
                continue;
            }
        }
        return $output . implode(', ', $args) . ')';
    }

    protected function compileValue(SqlValue $value): string
    {
        if ($value instanceof SqlValueArray) {
            $vals = [];
            foreach ($value->getValue() as $val) {
                $vals[] = ':' . $this->addValue($val);
            }
            return '(' . join(', ', $vals) . ')';
        }
        return ':' . $this->addValue($value->getValue());
    }

    protected function compileDatetime(DateTimeInterface $datetime)
    {
        return $datetime->format('Y-m-d H:i:s');
    }

    protected function compileOrderBy(SortableInterface $sortable)
    {
        $orderBy = $sortable->getOrderBy();
        if (empty($orderBy)) {
            return '';
        }

        $output = ' ORDER BY ';
        $orders = [];
        foreach ($orderBy as $ob) {
            $col = $ob[0];
            $mode = $ob[1];
            $orders[] = "{$this->compileComparable($col)} {$mode}";
        }
        $output .= implode(', ', $orders);
        return $output;
    }

    protected function compileGroupBy(SelectQuery $select): string
    {
        $groupBy = $select->getGroupBy();
        if (empty($groupBy)) {
            return '';
        }

        $output = ' GROUP BY ';
        $groups = [];
        foreach ($groupBy as $g) {
            $groups[] = $this->compileComparable($g);
        }
        $output .= implode(', ', $groups);
        return $output;
    }

    protected function compileLimit(LimitableInterface $limitable)
    {
        $limit = $limitable->getLimit();
        if (is_null($limit)) {
            return '';
        }
        $offset = $limitable->getLimitOffset();

        $output = ' LIMIT ' . $limit;
        $output .= $offset > 0 ? ', ' . $offset : '';
        return $output;
    }
}
