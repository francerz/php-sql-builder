<?php

namespace Francerz\SqlBuilder\Traits;

use Francerz\SqlBuilder\Expressions\Logical\ConditionList;

trait GroupableTrait
{
    protected $having;

    public function __construct()
    {
        $this->having = new ConditionList();
    }

    public function groupBy($group)
    {
        return $this;
    }

    public function having()
    {
        return $this->having;
    }

    protected function __clone()
    {
        $this->having = clone $this->having;
    }
}