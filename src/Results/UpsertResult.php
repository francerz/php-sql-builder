<?php

namespace Francerz\SqlBuilder\Results;

use Francerz\SqlBuilder\CompiledQuery;

class UpsertResult extends InsertResult
{
    private $insertRows = 0;
    private $updateRows = 0;

    public function __construct(
        CompiledQuery $query,
        int $insertRows = 0,
        int $updateRows = 0,
        $firstId = null,
        bool $success = true
    ) {
        parent::__construct($query, $insertRows + $updateRows, $firstId, $success);
        $this->insertRows = $insertRows;
        $this->updateRows = $updateRows;
    }

    public static function fromInsertResult(InsertResult $result)
    {
        return new static(
            $result->getQuery(),
            $result->getNumRows(),
            0,
            $result->getFirstId(),
            $result->success()
        );
    }

    public static function fromResult(
        QueryResultInterface $result,
        int $insertRows = 0,
        int $updateRows = 0,
        $firstId = null
    ) {
        return new static(
            $result->getQuery(),
            $insertRows,
            $updateRows,
            $firstId,
            true
        );
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getInsertRows()
    {
        return $this->insertRows;
    }

    public function getUpdateRows()
    {
        return $this->updateRows;
    }
}
