<?php

namespace Francerz\SqlBuilder\Expressions\Logical;

use Francerz\SqlBuilder\Expressions\BooleanResultInterface;

class ConditionItem
{
    private $condition;
    private $connector;

    public function __construct(BooleanResultInterface $condition, $connector = LogicConnectors::AND)
    {
        $this->condition = $condition;
        $this->connector = $connector;
    }

    public function setCondition(BooleanResultInterface $condition)
    {
        $this->condition = $condition;
    }
    
    public function getCondition()
    {
        return $this->condition;
    }

    public function getConnector()
    {
        return $this->connector;
    }

    public function __clone()
    {
        $this->condition = clone $this->condition;
    }
}