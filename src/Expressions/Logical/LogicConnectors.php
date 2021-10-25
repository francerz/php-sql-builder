<?php

namespace Francerz\SqlBuilder\Expressions\Logical;

use Francerz\Enum\AbstractEnum;

final class LogicConnectors extends AbstractEnum
{
    public const AND = 'AND';
    public const OR = 'OR';
}
