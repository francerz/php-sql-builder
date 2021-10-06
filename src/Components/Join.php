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
        switch ($joinType) {
            case JoinTypes::CROSS_JOIN:
            case JoinTypes::INNER_JOIN:
            case JoinTypes::LEFT_JOIN:
            case JoinTypes::LEFT_OUTER_JOIN:
            case JoinTypes::RIGHT_JOIN:
            case JoinTypes::RIGHT_OUTER_JOIN:
            case JoinTypes::FULL_OUTER_JOIN:
                $this->joinType = $joinType;
                break;
            default:
                throw new InvalidArgumentException('Invalid JoinType.');
        }
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
