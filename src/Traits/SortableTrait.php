<?php

namespace Francerz\SqlBuilder\Traits;

use Francerz\SqlBuilder\Expressions\ComparableComponentInterface;
use Francerz\SqlBuilder\Query;

trait SortableTrait
{
    private $orderBy = [];

    public function orderBy($column, $mode='ASC')
    {
        if (!$column instanceof ComparableComponentInterface) {
            $column = Query::column($column);
        }
        $this->orderBy[] = [$column, $mode];
        return $this;
    }

    public function getOrderBy() : array
    {
        return $this->orderBy;
    }
}