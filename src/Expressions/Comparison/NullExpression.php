<?php

namespace Francerz\SqlBuilder\Expressions\Comparison;

use Francerz\SqlBuilder\Expressions\ComparableComponentInterface;
use Francerz\SqlBuilder\Expressions\NegatableInterface;
use InvalidArgumentException;

class NullExpression implements
    ComparisonOperationInterface,
    NegatableInterface
{
    private $negated = false;
    private $operand;

    public function __construct(ComparableComponentInterface $operand, $negated = false)
    {
        $this->setOperand($operand);
        $this->negate($negated);
    }

    public function setOperand($operand)
    {
        if (!$operand instanceof ComparableComponentInterface) {
            throw new InvalidArgumentException('Invalid $operand value.');
        }
        $this->operand = $operand;
    }
    public function getOperand()
    {
        return $this->operand;
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