<?php

namespace Francerz\SqlBuilder\Results;

use Francerz\SqlBuilder\CompiledQuery;

class InsertResult extends AbstractResult
{
    private $firstId;

    public function __construct(CompiledQuery $query, int $numRows = 0, $firstId = null)
    {
        parent::__construct($query, $numRows);
        $this->firstId = $firstId;
    }

    public function getFirstId()
    {
        return $this->firstId;
    }
}