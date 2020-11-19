<?php

namespace Francerz\SqlBuilder\Results;

use Francerz\SqlBuilder\CompiledQuery;

interface QueryResultInterface
{
    public function success() : bool;
    public function getQuery() : CompiledQuery;
    public function getNumRows() : int;
}