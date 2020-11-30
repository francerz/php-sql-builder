<?php

namespace Francerz\SqlBuilder\Traits;

use Francerz\SqlBuilder\Expressions\Comparison\ComparisonModes;
use Francerz\SqlBuilder\Expressions\Logical\ConditionList;

trait WhereableTrait
{
    protected $where;

    public function __construct()
    {
        $this->where = new ConditionList(ComparisonModes::COLUMN_VALUE);
    }

    public function where() : ConditionList
    {
        $where = $this->where;
        $args = func_get_args();
        if (!empty($args)) {
            call_user_func_array($where, $args);
        }
        return $where;
    }

    protected function __clone()
    {
        $this->where = clone $this->where;
    }
}