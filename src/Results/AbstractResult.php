<?php

namespace Francerz\SqlBuilder\Results;

use Francerz\SqlBuilder\Compiler\CompiledQuery;

class AbstractResult
{
    protected $query;
    protected $numRows;

    public function __construct(CompiledQuery $query, int $numRows = 0)
    {
        $this->query = $query;
        $this->numRows = $numRows;
    }

    public function getQuery()
    {
        return $this->query;
    }
    public function getNumRows()
    {
        return $this->numRows;
    }
}