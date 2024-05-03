<?php

namespace Francerz\SqlBuilder\Results;

class AbstractResult implements QueryResultInterface
{
    protected $query;
    protected $numRows;
    private $success;

    public function __construct(int $numRows = 0, bool $success = true)
    {
        $this->numRows = $numRows;
        $this->success = $success;
    }

    public function success(): bool
    {
        return $this->success;
    }

    public function getNumRows(): int
    {
        return $this->numRows;
    }
}
