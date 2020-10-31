<?php

namespace Francerz\SqlBuilder\Expressions\Comparison;

use Francerz\SqlBuilder\Expressions\ComparableComponentInterface;
use InvalidArgumentException;

class RelationalExpression implements
    ComparisonOperationInterface
{

    private $operand1;
    private $operand2;
    private $operator;

    public function __construct(
        ComparableComponentInterface $operand1,
        ComparableComponentInterface $operand2,
        $operator = null)
    {
        $this->operand1 = $operand1;
        $this->operand2 = $operand2;
        $this->operator = is_null($operator) ? RelationalOperators::EQUALS : $operator;
    }

    public function setOperand1($operand)
    {
        if (!$operand instanceof ComparableComponentInterface) {
            throw new InvalidArgumentException('Invalid $operand value.');
        }
        $this->operand1 = $operand;
    }
    public function getOperand1()
    {
        return $this->operand1;
    }

    public function setOperand2($operand)
    {
        if (!$operand instanceof ComparableComponentInterface) {
            throw new InvalidArgumentException('Invalid $operand value.');
        }
        $this->operand2 = $operand;
    }
    public function getOperand2()
    {
        return $this->operand2;
    }

    public function setOperator($operator)
    {
        if (!in_array($operator, array(
            RelationalOperators::EQUALS, RelationalOperators::NOT_EQUALS,
            RelationalOperators::LESS, RelationalOperators::LESS_EQUALS,
            RelationalOperators::GREATER, RelationalOperators::GREATER_EQUALS
        ))) {
            throw new InvalidArgumentException('Invalid operator.');
        }
        $this->operator = $operator;
    }
    public function getOperator()
    {
        return $this->operator;
    }
}