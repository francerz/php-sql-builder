<?php

namespace Francerz\SqlBuilder\Nesting;

class RowProxy
{
    private $current;

    public function __get($name)
    {
        return new ValueProxy($this, $name);
    }

    public function setCurrent($row)
    {
        $this->current = $row;
    }
    public function getCurrent()
    {
        return $this->current;
    }
}
