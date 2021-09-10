<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\Components\Column;
use Francerz\SqlBuilder\Components\Table;
use Francerz\SqlBuilder\Components\TableReference;
use Francerz\SqlBuilder\Expressions\ComparableComponentInterface;
use Francerz\SqlBuilder\Expressions\Logical\ConditionList;
use Francerz\SqlBuilder\Traits\GroupableTrait;
use Francerz\SqlBuilder\Traits\JoinableTrait;
use Francerz\SqlBuilder\Traits\LimitableInterface;
use Francerz\SqlBuilder\Traits\LimitableTrait;
use Francerz\SqlBuilder\Traits\NestableTrait;
use Francerz\SqlBuilder\Traits\SortableInterface;
use Francerz\SqlBuilder\Traits\SortableTrait;
use Francerz\SqlBuilder\Traits\WhereableTrait;

class SelectQuery implements QueryInterface, LimitableInterface, SortableInterface
{
    use JoinableTrait, WhereableTrait, NestableTrait, GroupableTrait, LimitableTrait, SortableTrait {
        WhereableTrait::__construct as private Whereable__construct;
        GroupableTrait::__construct as private Groupable__construct;
        WhereableTrait::__clone as private WhereableTrait__clone;
        GroupableTrait::__clone as private GroupableTrait__clone;
        NestableTrait::__construct as private Nestable__construct;
        JoinableTrait::join as private JoinableTrait_join;
    }

    protected $from;
    protected $columns;

    public function __construct($table = null, ?array $columns = null)
    {
        $this->Whereable__construct();
        $this->Groupable__construct();
        $this->Nestable__construct();
        $this->columns = [];
        if (isset($table)) {
            $this->from($table, $columns);
        }
    }

    public static function createSelect($table = null, ?array $columns = null, array $matches = [])
    {
        $select = new static($table, $columns);
        foreach ($matches as $key => $value) {
            if ($value instanceof ComparableComponentInterface) {
                $select->where($value);
                continue;
            }
            $select->where($key, $value);
        }
        return $select;
    }

    public function from($table, ?array $columns = null)
    {
        if (!$table instanceof Table) {
            $table = Table::fromExpression($table);
        }
        $this->from = new TableReference($table, $columns);
        return $this;
    }

    public function getFrom() : TableReference
    {
        return $this->from;
    }

    public function getTable() : Table
    {
        return $this->from->getTable();
    }

    public function getTableReference(string $aliasOrName)
    {
        $table = $this->from->getTable()->getAliasOrName();
        if ($aliasOrName == $table) {
            return $this->from;
        }

        $join = $this->getJoin($aliasOrName);
        if (isset($join)) {
            return $join->getTableReference();
        }
        return null;
    }

    public function __clone()
    {
        $this->WhereableTrait__clone();
        $this->GroupableTrait__clone();
    }

    public function columns($column, ...$moreColumns)
    {
        if (is_array($column)) {
            $column = Column::fromArray($column);
            $this->columns = array_merge($this->columns, $column);
        } elseif (is_string($column)) {
            $this->columns[] = Column::fromString($column);
        } elseif ($column instanceof Column) {
            $this->columns[] = $column;
        }
        if (!empty($moreColumns)) {
            foreach($moreColumns as $c) {
                $this->columns($c);
            }
        }
        return $this;
    }

    /**
     * @return Column[]
     */
    public function getAllColumns()
    {
        $columns = array_merge($this->columns, $this->from->getColumns());
        $joins = $this->getJoins();
        foreach ($joins as $join) {
            $columns = array_merge($columns, $join->getTableReference()->getColumns());
        }
        return $columns;
    }

    public function getColumn(string $aliasOrName)
    {
        $columns = $this->getAllColumns();
        $columns = array_filter($columns, function(Column $col) use ($aliasOrName) {
            return $aliasOrName == $col->getAliasOrName();
        });
        $first = reset($columns);
        return $first === false ? null : $first;
    }

    public function whereSingle($column, ...$args) : ConditionList
    {
        $value = end($args);
        if (is_scalar($value)) {
            $this->limit(1);
        }
        return call_user_func_array([$this, 'where'], func_get_args());
    }

    #region After Excecute
    private $actions = [];

    public function afterExecute(callable $action)
    {
        $this->actions[] = $action;
    }

    public function getAfterExecuteActions()
    {
        return $this->actions;
    }
    #endregion
}