<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\CompiledQuery;
use Francerz\SqlBuilder\Results\QueryResultInterface;

interface DriverInterface
{
    public function connect(ConnectParams $params);
    public function execute(CompiledQuery $query) : QueryResultInterface;
}