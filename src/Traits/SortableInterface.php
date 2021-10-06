<?php

namespace Francerz\SqlBuilder\Traits;

interface SortableInterface
{
    public function orderBy($column, $mode = 'ASC');
    public function getOrderBy(): array;
}
