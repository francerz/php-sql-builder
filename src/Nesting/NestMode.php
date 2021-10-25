<?php

namespace Francerz\SqlBuilder\Nesting;

use Francerz\Enum\AbstractEnum;

final class NestMode extends AbstractEnum
{
    public const COLLECTION   = 0b01;
    public const SINGLE_FIRST = 0b10;
    public const SINGLE_LAST  = 0b11;
}
