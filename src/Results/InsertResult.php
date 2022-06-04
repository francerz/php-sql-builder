<?php

namespace Francerz\SqlBuilder\Results;

class InsertResult extends AbstractResult
{
    private $insertedId;

    public function __construct(int $numRows = 0, $insertedId = null, bool $success = true)
    {
        parent::__construct($numRows, $success);
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
