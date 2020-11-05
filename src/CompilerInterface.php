<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\QueryInterface;

interface CompilerInterface 
{
    public function compile(QueryInterface $query) : ?CompiledQuery;
}