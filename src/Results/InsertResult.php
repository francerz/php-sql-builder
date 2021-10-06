<?php

namespace Francerz\SqlBuilder\Results;

use Francerz\SqlBuilder\CompiledQuery;

class InsertResult extends AbstractResult
{
    private $insertedId;

    public function __construct(CompiledQuery $query, int $numRows = 0, $insertedId = null, bool $success = true)
    {
        parent::__construct($query, $numRows, $success);
        $this->insertedId = $insertedId;
    }

    public function getFirstId()
    {
        return $this->insertedId;
    }

    public function getInsertedId()
    {
        return $this->insertedId;
    }
}
