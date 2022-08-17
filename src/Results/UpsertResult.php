<?php

namespace Francerz\SqlBuilder\Results;

class UpsertResult extends InsertResult
{
    private $inserts = [];
    private $updates = [];
    private $insertRows = 0;
    private $updateRows = 0;

    public function __construct(
        array $inserts = [],
        array $updates = [],
        int $insertRows = 0,
        int $updateRows = 0,
        $firstId = null,
        bool $success = true
    ) {
        parent::__construct($insertRows + $updateRows, $firstId, $success);
        $this->inserts = $inserts;
        $this->updates = $updates;
        $this->insertRows = $insertRows;
        $this->updateRows = $updateRows;
    }

    public function getInsertRows()
    {
        return $this->insertRows;
    }

    public function getUpdateRows()
    {
        return $this->updateRows;
    }

    public function getInserts()
    {
        return $this->inserts;
    }

    public function getUpdates()
    {
        return $this->updates;
    }
}
