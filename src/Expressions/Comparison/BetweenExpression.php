<?php

namespace Francerz\SqlBuilder\Expressions\Comparison;

use Francerz\SqlBuilder\Expressions\ComparableComponentInterface;
use Francerz\SqlBuilder\Expressions\NegatableInterface;
use InvalidArgumentException;

class BetweenExpression implements
    ComparisonOperationInterface,
    NegatableInterface
{
    private $operand1;
    private $operand2;
    private $operand3;
    private $negated;

    public function __construct(
        ComparableComponentInterface $operand1,
        ComparableComponentInterface $operand2,
        ComparableComponentInterface $operand3,
        bool $negated = false
    ) {
        $this->operand1 = $operand1;
        $this->operand2 = $operand2;
        $this->operand3 = $operand3;
        $this->negated = $negated;
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

    public function setOperand3($operand)
    {
        if (!$operand instanceof ComparableComponentInterface) {
            throw new InvalidArgumentException('Invalid $operand value.');
        }
        $this->operand3 = $operand;
    }
    public function getOperand3()
    {
        return $this->operand3;
    }

    public function negate(bool $negate = true)
    {
        $this->negated = $negate;
    }
    public function isNegated(): bool
    {
        return $this->negated;
    }
}