<?php

namespace Francerz\SqlBuilder\Components;

use Francerz\SqlBuilder\Expressions\ComparableComponentInterface;

class SqlValue implements ComparableComponentInterface
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
    public function getValue()
    {
        return $this->value;
    }
}