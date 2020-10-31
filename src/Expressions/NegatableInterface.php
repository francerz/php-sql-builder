<?php

namespace Francerz\SqlBuilder\Expressions;

interface NegatableInterface
{
    public function negate(bool $negate = true);
    public function isNegated() : bool;
}