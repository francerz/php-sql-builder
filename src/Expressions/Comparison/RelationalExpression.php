<?php

namespace Francerz\SqlBuilder\Expressions\Comparison;

use Francerz\SqlBuilder\Expressions\ComparableComponentInterface;
use Francerz\SqlBuilder\Expressions\NegatableInterface;
use Francerz\SqlBuilder\Expressions\TwoOperandsInterface;
use Francerz\SqlBuilder\Nesting\NestOperationResolverInterface;
use Francerz\SqlBuilder\Nesting\NestTranslator;
use Francerz\SqlBuilder\Nesting\ValueProxy;
use Francerz\SqlBuilder\Results\SelectResult;
use InvalidArgumentException;
use RuntimeException;

class RelationalExpression implements
    ComparisonOperationInterface,
    TwoOperandsInterface,
    NegatableInterface,
    NestOperationResolverInterface
{

    private $operand1;
    private $operand2;
    private $operator;

    private $negated = false;

    public function __construct(
        ComparableComponentInterface $operand1,
        ComparableComponentInterface $operand2,
        $operator = null
    ) {
        $this->operand1 = $operand1;
        $this->operand2 = $operand2;
        $this->setOperator($operator);
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
        $this->operator =
            RelationalOperators::coerce($operator) ??
            RelationalOperators::fromValue(RelationalOperators::EQUALS);
    }
    public function getOperator()
    {
        return $this->operator;
    }

    public function negate(bool $negate = true)
    {
        $this->negated = $negate;
    }

    public function isNegated(): bool
    {
        return $this->negated;
    }

    public function requiresTransform(): bool
    {
        return $this->operand1 instanceof ValueProxy || $this->operand2 instanceof ValueProxy;
    }

    public function nestTransform(SelectResult $parentResult): ?ComparisonOperationInterface
    {
        if (in_array($this->operator, [RelationalOperators::EQUALS, RelationalOperators::NOT_EQUALS])) {
            if ($this->operand1 instanceof ValueProxy) {
                return new InExpression(
                    $this->operand2,
                    NestTranslator::valueProxyToArray($this->operand1, $parentResult),
                    $this->operator->is(RelationalOperators::NOT_EQUALS)
                );
            }
            if ($this->operand2 instanceof ValueProxy) {
                return new InExpression(
                    $this->operand1,
                    NestTranslator::valueProxyToArray($this->operand2, $parentResult),
                    $this->operator->is(RelationalOperators::NOT_EQUALS)
                );
            }
        } elseif ($this->operator->in([RelationalOperators::LESS, RelationalOperators::LESS_EQUALS])) {
            if ($this->operand1 instanceof ValueProxy) {
                $this->operand1 = NestTranslator::valueProxyToMin($this->operand1, $parentResult);
            }
            if ($this->operand2 instanceof ValueProxy) {
                $this->operand2 = NestTranslator::valueProxyToMax($this->operand2, $parentResult);
            }
            return $this;
        } elseif ($this->operator->in([RelationalOperators::GREATER, RelationalOperators::GREATER_EQUALS])) {
            if ($this->operand1 instanceof ValueProxy) {
                $this->operand1 = NestTranslator::valueProxyToMax($this->operand1, $parentResult);
            }
            if ($this->operand2 instanceof ValueProxy) {
                $this->operand2 = NestTranslator::valueProxyToMin($this->operand2, $parentResult);
            }
            return $this;
        }
        return null;
    }

    public function nestResolve(): bool
    {
        if (!$this->operand1 instanceof ValueProxy) {
            throw new RuntimeException('Invalid operand1 for ValueProxyResolver');
        }
        if (!$this->operand2 instanceof ValueProxy) {
            throw new RuntimeException('Invalid operand2 for ValueProxyResolver');
        }
        $op1 = $this->operand1->getValue();
        $op2 = $this->operand2->getValue();

        switch ((string)$this->operator) {
            case RelationalOperators::EQUALS:
                return $op1 == $op2;
            case RelationalOperators::NOT_EQUALS:
                return $op1 != $op2;
            case RelationalOperators::GREATER:
                return $op1 > $op2;
            case RelationalOperators::GREATER_EQUALS:
                return $op1 >= $op2;
            case RelationalOperators::LESS:
                return $op1 < $op2;
            case RelationalOperators::LESS_EQUALS:
                return $op1 <= $op2;
        }
        return false;
    }
}
