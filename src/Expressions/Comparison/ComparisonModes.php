<?php

namespace Francerz\SqlBuilder\Expressions\Comparison;

interface ComparisonModes
{
    const COLUMN_COLUMN = 0x00;
    const COLUMN_VALUE  = 0x01;
    const VALUE_COLUMN  = 0x10;
    const VALUE_VALUE   = 0x11;
}