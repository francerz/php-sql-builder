<?php

namespace Francerz\SqlBuilder\Traits;

use Francerz\SqlBuilder\Components\Join;
use Francerz\SqlBuilder\Components\JoinTypes;
use Francerz\SqlBuilder\Components\Table;
use Francerz\SqlBuilder\Components\TableReference;
use LogicException;

Trait JoinableTrait
{
    protected $joins = [];
    protected $lastJoin;

    public function join($table, array $columns = [], $joinType = JoinTypes::INNER_JOIN)
    {
        if (!$table instanceof Table) {
            $table = Table::fromExpression($table);
        }
        $join = new Join(new TableReference($table, $columns), $joinType);
        $alias = $join->getTableReference()->getTable()->getAlias();
        $this->joins[$alias] = $join;
        $this->lastJoin = $join;
        return $this;
    }
    public function crossJoin($table, array $columns = [])
    {
        return $this->join($table, $columns, JoinTypes::CROSS_JOIN);   
    }
    public function innerJoin($table, array $columns = [])
    {
        return $this->join($table, $columns, JoinTypes::INNER_JOIN);
    }
    public function leftJoin($table, array $columns = [])
    {
        return $this->join($table, $columns, JoinTypes::LEFT_JOIN);
    }
    public function rightJoin($table, array $columns = [])
    {
        return $this->join($table, $columns, JoinTypes::RIGHT_JOIN);
    }
    public function leftOuterJoin($table, array $columns = [])
    {
        return $this->join($table, $columns, JoinTypes::LEFT_OUTER_JOIN);
    }
    public function rightOuterJoin($table, array $columns = [])
    {
        return $this->join($table, $columns, JoinTypes::RIGHT_OUTER_JOIN);
    }

    public function getJoins()
    {
        return $this->joins;
    }

    public function isJoined(string $aliasOrName)
    {
        return isset($this->joins[$aliasOrName]);
    }

    public function on()
    {
        if (!$this->lastJoin instanceof Join) {
            throw new LogicException('Cannot use \'on\' clause without join.');
        }
        return $this->lastJoin->getOn();
    }
}