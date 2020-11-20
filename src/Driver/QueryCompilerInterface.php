<?php

namespace Francerz\SqlBuilder\Driver;

use Francerz\SqlBuilder\CompiledQuery;
use Francerz\SqlBuilder\QueryInterface;

interface QueryCompilerInterface 
{
    public function compileQuery(QueryInterface $query) : ?CompiledQuery;
}