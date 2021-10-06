<?php

namespace Francerz\SqlBuilder\Expressions\Comparison;

use Francerz\SqlBuilder\Expressions\ComparableComponentInterface;
use Francerz\SqlBuilder\Expressions\NegatableInterface;
use Francerz\SqlBuilder\Expressions\OneOperandInterface;
use Francerz\SqlBuilder\Nesting\NestOperationResolverInterface;
use Francerz\SqlBuilder\Nesting\ValueProxy;
use Francerz\SqlBuilder\Results\SelectResult;
use InvalidArgumentException;
use RuntimeException;

class NullExpression implements
    ComparisonOperationInterface,
    NegatableInterface,
    OneOperandInterface,
    NestOperationResolverInterface
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

    public function requiresTransform(): bool
    {
        return false;
    }

    public function nestTransform(SelectResult $parentResult): ?ComparisonOperationInterface
    {
        return null;
    }

    public function nestResolve(): bool
    {
        if (!$this->operand instanceof ValueProxy) {
            throw new RuntimeException('Invalid operand for ValueProxyResolver');
        }
        return is_null($this->operand->getValue());
    }
}
