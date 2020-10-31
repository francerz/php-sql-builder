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
        $this->joinType = $joinType;
        $this->on = new ConditionList(ComparisonModes::COLUMN_COLUMN);
    }

    public function setJoinType($joinType)
    {
        if (!in_array($joinType, array(
            JoinTypes::CROSS_JOIN, JoinTypes::INNER_JOIN,
            JoinTypes::LEFT_JOIN, JoinTypes::LEFT_OUTER_JOIN,
            JoinTypes::RIGHT_JOIN, JoinTypes::RIGHT_OUTER_JOIN,
            JoinTypes::FULL_OUTER_JOIN
        ))) {
            throw new InvalidArgumentException('Invalid JoinType.');
        }
        $this->joinType = $joinType;
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