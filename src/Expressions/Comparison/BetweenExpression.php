<?php

namespace Francerz\SqlBuilder\Expressions\Comparison;

use Francerz\SqlBuilder\Components\Nest;
use Francerz\SqlBuilder\Expressions\ComparableComponentInterface;
use Francerz\SqlBuilder\Expressions\NegatableInterface;
use Francerz\SqlBuilder\Expressions\ThreeOperandsInterface;
use Francerz\SqlBuilder\Nesting\NestOperationResolverInterface;
use Francerz\SqlBuilder\Nesting\NestTranslator;
use Francerz\SqlBuilder\Nesting\ValueProxy;
use Francerz\SqlBuilder\Results\SelectResult;
use InvalidArgumentException;

class BetweenExpression implements
    ComparisonOperationInterface,
    NegatableInterface,
    ThreeOperandsInterface,
    NestOperationResolverInterface
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

    public function nestTransform(SelectResult $parentResult): ?ComparisonOperationInterface
    {
        if ($this->operand1 instanceof ValueProxy && $this->operand2 instanceof ValueProxy) {
            if (!$this->negated) {
                $this->operand1 = NestTranslator::valueProxyToMin($this->operand1, $parentResult);
                $this->operand2 = NestTranslator::valueProxyToMax($this->oprand2, $parentResult);
                return $this;
            }
        }
        return null;
    }
    public function nestResolve(): bool
    {
        return true;
    }
}