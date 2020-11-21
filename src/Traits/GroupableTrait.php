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
        $having = $this->having;
        $args = func_get_args();
        if (!empty($args)) {
            call_user_func_array($having, $args);
        }
        return $having;
    }

    protected function __clone()
    {
        $this->having = clone $this->having;
    }
}