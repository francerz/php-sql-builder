<?php

namespace Francerz\SqlBuilder\Expressions\Comparison;

use Francerz\SqlBuilder\Expressions\BooleanResultInterface;
use Francerz\SqlBuilder\Expressions\OperationInterface;

interface ComparisonOperationInterface extends
    OperationInterface,
    BooleanResultInterface
{
}