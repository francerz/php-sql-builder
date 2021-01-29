<?php

namespace Francerz\SqlBuilder\Results;

use Francerz\SqlBuilder\CompiledQuery;

class UpsertResult extends InsertResult
{
    const ACTION_INSERT = 1;
    const ACTION_UPDATE = 2;

    private $action;

    public function __construct(int $action, CompiledQuery $query, int $numRows = 0, $firstId = null, bool $success = true)
    {
        parent::__construct($query, $numRows, $firstId, $success);
        $this->action = $action;
    }

    public static function fromInsertResult(InsertResult $result)
    {
        return new static(
            static::ACTION_INSERT,
            $result->getQuery(),
            $result->getNumRows(),
            $result->getFirstId(),
            $result->success()
        );
    }

    public static function fromUpdateResult(UpdateResult $result, ?int $numRows = null, $firstId = null)
    {
        return new static(
            static::ACTION_UPDATE,
            $result->getQuery(),
            is_null($numRows) ? $result->getNumRows() : $numRows,
            $firstId,
            $result->success()
        );
    }

    public function getAction()
    {
        return $this->action;
    }

    public function isInsert() : bool
    {
        return $this->action === static::ACTION_INSERT;
    }

    public function isUpdate() : bool
    {
        return $this->action === static::ACTION_UPDATE;
    }
}