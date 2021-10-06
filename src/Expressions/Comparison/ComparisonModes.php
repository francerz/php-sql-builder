<?php

namespace Francerz\SqlBuilder\Expressions\Comparison;

interface ComparisonModes
{
    public const COLUMN_COLUMN = 0x00;
    public const COLUMN_VALUE  = 0x01;
    public const VALUE_COLUMN  = 0x10;
    public const VALUE_VALUE   = 0x11;
}
