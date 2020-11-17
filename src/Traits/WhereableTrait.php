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

    public function where()
    {
        return $this->where;
    }
}