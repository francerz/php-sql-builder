<?php

namespace Francerz\SqlBuilder\Components;

class Set
{
    private $column;
    private $value;

    public function __construct(Column $column, $value)
    {
        $this->column = $column;
        $this->value = $value;
    }

    public function getColumn()
    {
        return $this->column;
    }

    public function getValue()
    {
        return $this->value;
    }
}
