<?php

namespace Francerz\SqlBuilder\Compiler;

use Francerz\SqlBuilder\QueryInterface;

interface CompilerInterface 
{
    public function compile(QueryInterface $query) : ?CompiledQuery;
}