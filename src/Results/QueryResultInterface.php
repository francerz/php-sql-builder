<?php

namespace Francerz\SqlBuilder\Results;

use Francerz\SqlBuilder\CompiledQuery;

interface QueryResultInterface
{
    public function success(): bool;
    public function getNumRows(): int;
}
