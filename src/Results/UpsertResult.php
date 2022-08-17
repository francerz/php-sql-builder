<?php

namespace Francerz\SqlBuilder\Results;

class UpsertResult extends InsertResult
{
    private $inserts = [];
    private $updates = [];
    private $numInserted = 0;
    private $numUpdated = 0;

    public function __construct(
        array $inserts = [],
        array $updates = [],
        int $numInsertedRows = 0,
        int $numUpdatedRows = 0,
        $firstId = null,
        bool $success = true
    ) {
        parent::__construct($numInsertedRows + $numUpdatedRows, $firstId, $success);
        $this->inserts = $inserts;
        $this->updates = $updates;
        $this->numInserted = $numInsertedRows;
        $this->numUpdated = $numUpdatedRows;
    }

    /**
     * @deprecated
     */
    public function getInsertRows()
    {
        return $this->numInserted;
    }

    /**
     * @deprecated
     */
    public function getUpdateRows()
    {
        return $this->numUpdated;
    }

    public function getNumInserted()
    {
        return $this->numInserted;
    }

    public function getNumUpdated()
    {
        return $this->numUpdated;
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
