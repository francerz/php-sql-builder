<?php

namespace Francerz\SqlBuilder\Results;

use Francerz\SqlBuilder\CompiledQuery;

class AbstractResult implements QueryResultInterface
{
    protected $query;
    protected $numRows;

    public function __construct(CompiledQuery $query, int $numRows = 0, bool $success = true)
    {
        $this->query = $query;
        $this->numRows = $numRows;
        $this->success = $success;
    }

    public function success() : bool
    {
        return $this->success;
    }

    public function getQuery() : CompiledQuery
    {
        return $this->query;
    }
    public function getNumRows() : int
    {
        return $this->numRows;
    }
}