<?php

namespace Francerz\SqlBuilder\Traits;

use Francerz\SqlBuilder\Expressions\ComparableComponentInterface;
use Francerz\SqlBuilder\Query;

trait SortableTrait
{
    private $orderBy = [];

    public function orderBy($column, $mode = 'ASC')
    {
        if (is_array($column)) {
            foreach ($column as $k => $v) {
                if (is_numeric($k)) {
                    $this->orderBy($v);
                    continue;
                }
                $this->orderBy($k, $v);
            }
            return $this;
        }
        if (!$column instanceof ComparableComponentInterface) {
            $column = Query::column($column);
        }
        $this->orderBy[] = [$column, $mode];
        return $this;
    }

    public function getOrderBy(): array
    {
        return $this->orderBy;
    }
}
