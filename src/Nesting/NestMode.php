<?php

namespace Francerz\SqlBuilder\Nesting;

interface NestMode
{
    const COLLECTION = 0x01;
    const SINGLE_FIRST = 0x10;
    const SINGLE_LAST = 0x11;
}