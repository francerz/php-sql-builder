<?php

namespace Francerz\SqlBuilder\Expressions\Comparison;

use Francerz\Enum\AbstractEnum;

final class ComparisonModes extends AbstractEnum
{
    public const COLUMN_COLUMN = 0b00;
    public const COLUMN_VALUE  = 0b01;
    public const VALUE_COLUMN  = 0b10;
    public const VALUE_VALUE   = 0b11;
}
