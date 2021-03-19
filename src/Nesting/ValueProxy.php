<?php

namespace Francerz\SqlBuilder\Nesting;

use Francerz\SqlBuilder\Expressions\ComparableComponentInterface;

class ValueProxy implements ComparableComponentInterface
{
    private $row;
    private $name;
    private $isParent = false;

    public function __construct(RowProxy $row, string $name)
    {
        $this->row = $row;
        $this->name = $name;
    }

    public function getRow()
    {
        return $this->row;
    }

    public function getName()
    {
        return $this->name;
    }

    public function isParent(bool $isParent = true)
    {
        $this->isParent = $isParent;
    }
    
    public function getIsParent() : bool
    {
        return $this->isParent;
    }

    public function getValue()
    {
        $row = $this->row->getCurrent();
        if (is_object($row)) {
            return $row->{$this->name};
        }
        return null;
    }
}