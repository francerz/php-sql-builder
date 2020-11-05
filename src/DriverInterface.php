<?php

namespace Francerz\SqlBuilder\Drivers;

use Francerz\SqlBuilder\CompiledQuery;
use Francerz\SqlBuilder\Results\QueryResultInterface;

interface DriverInterface
{
    public function execute(CompiledQuery $query) : QueryResultInterface;
}