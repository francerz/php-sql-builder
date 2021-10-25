<?php

namespace Francerz\SqlBuilder\Expressions\Comparison;

use Francerz\Enum\AbstractEnum;

final class RelationalOperators extends AbstractEnum
{
    public const EQUALS = '=';
    public const LESS = '<';
    public const GREATER = '>';
    public const LESS_EQUALS = '<=';
    public const GREATER_EQUALS = '>=';
    public const NOT_EQUALS = '<>';
}
