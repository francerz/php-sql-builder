<?php

namespace Francerz\SqlBuilder\Nesting;

interface NestMode
{
    public const COLLECTION = 0x01;
    public const SINGLE_FIRST = 0x10;
    public const SINGLE_LAST = 0x11;
}
