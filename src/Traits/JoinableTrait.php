<?php

namespace Francerz\SqlBuilder\Traits;

use Francerz\SqlBuilder\Components\Join;
use Francerz\SqlBuilder\Components\JoinTypes;
use Francerz\SqlBuilder\Components\Table;
use Francerz\SqlBuilder\Components\TableReference;
use Francerz\SqlBuilder\Expressions\Logical\ConditionList;
use LogicException;

trait JoinableTrait
{
    protected $joins = [];
    protected $lastJoin;

    public function join($table, ?array $columns = [], $joinType = JoinTypes::INNER_JOIN)
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
    public function crossJoin($table, ?array $columns = [])
    {
        return $this->join($table, $columns, JoinTypes::CROSS_JOIN);
    }
    public function innerJoin($table, ?array $columns = [])
    {
        return $this->join($table, $columns, JoinTypes::INNER_JOIN);
    }
    public function leftJoin($table, ?array $columns = [])
    {
        return $this->join($table, $columns, JoinTypes::LEFT_JOIN);
    }
    public function rightJoin($table, ?array $columns = [])
    {
        return $this->join($table, $columns, JoinTypes::RIGHT_JOIN);
    }
    public function leftOuterJoin($table, ?array $columns = [])
    {
        return $this->join($table, $columns, JoinTypes::LEFT_OUTER_JOIN);
    }
    public function rightOuterJoin($table, ?array $columns = [])
    {
        return $this->join($table, $columns, JoinTypes::RIGHT_OUTER_JOIN);
    }

    /**
     * Retrieves all Joins
     *
     * @return Join[]
     */
    public function getJoins()
    {
        return $this->joins;
    }

    /**
     * Retrieves a join with given alias or name
     *
     * @param string $aliasOrName
     * @return ?Join found Join with given alias or name
     */
    public function getJoin(string $aliasOrName)
    {
        return isset($this->joins[$aliasOrName]) ? $this->joins[$aliasOrName] : null;
    }

    public function isJoined(string $aliasOrName)
    {
        return isset($this->joins[$aliasOrName]);
    }

    public function on(): ConditionList
    {
        if (!$this->lastJoin instanceof Join) {
            throw new LogicException('Cannot use \'on\' clause without join.');
        }
        $on = $this->lastJoin->getOn();
        $args = func_get_args();
        if (!empty($args)) {
            call_user_func_array($on, $args);
        }
        return $on;
    }
}
