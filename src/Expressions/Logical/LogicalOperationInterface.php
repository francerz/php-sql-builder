<?php

namespace Francerz\SqlBuilder\Expressions\Logical;

use Francerz\SqlBuilder\Expressions\BooleanResultInterface;
use Francerz\SqlBuilder\Expressions\OperationInterface;

interface LogicalOperationInterface extends OperationInterface, BooleanResultInterface
{
}
