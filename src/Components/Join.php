<?php

namespace Francerz\SqlBuilder\Components;

use Francerz\SqlBuilder\Expressions\Logical\ConditionList;
use Francerz\SqlBuilder\Expressions\Comparison\ComparisonModes;
use InvalidArgumentException;

class Join
{
    private $joinType;
    private $tableReference;
    private $on;

    public function __construct(TableReference $tableReference, $joinType = JoinTypes::INNER_JOIN)
    {
        $this->tableReference = $tableReference;
        $this->joinType = JoinTypes::coerce($joinType);
        $this->on = new ConditionList(ComparisonModes::COLUMN_COLUMN);
    }

    /**
     * @param JoinTypes $joinType
     * @return void
     */
    public function setJoinType($joinType)
    {
        $this->joinType = JoinTypes::coerce($joinType);
    }

    public function getTableReference()
    {
        return $this->tableReference;
    }

    public function getJoinType()
    {
        return $this->joinType;
    }

    public function getOn()
    {
        return $this->on;
    }
}
