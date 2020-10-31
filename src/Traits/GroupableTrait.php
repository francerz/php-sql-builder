<?php

namespace Francerz\SqlBuilder\Traits;

use Francerz\SqlBuilder\Expressions\Logical\ConditionList;

trait GroupableTrait
{
    private $having;

    public function __construct()
    {
        $this->having = new ConditionList();
    }

    public function groupBy($group)
    {
        
    }

    public function having()
    {
        return $this->having;
    }
}