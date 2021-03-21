<?php

namespace Francerz\SqlBuilder\Nesting;

use Francerz\SqlBuilder\SelectQuery;

class NestedSelect
{
    private $select;
    private $conds;

    private $matches = [];

    public function __construct(?SelectQuery $select = null)
    {
        $this->select = isset($select) ? $select : new SelectQuery();
    }
    
    public function getSelect()
    {
        return $this->select;
    }
}