<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\Components\Table;
use Francerz\SqlBuilder\Traits\JoinableTrait;
use Francerz\SqlBuilder\Traits\LimitableInterface;
use Francerz\SqlBuilder\Traits\LimitableTrait;
use Francerz\SqlBuilder\Traits\SortableInterface;
use Francerz\SqlBuilder\Traits\SortableTrait;
use Francerz\SqlBuilder\Traits\WhereableTrait;

class DeleteQuery implements QueryInterface, LimitableInterface, SortableInterface
{
    use JoinableTrait, WhereableTrait, LimitableTrait, SortableTrait {
        WhereableTrait::__construct as private _whereableTraitConstruct;
    }

    private $connection = null;
    /** @var Table */
    private $table;
    /** @var string[] */
    private $rowsInArray;

    public function __construct($table)
    {
        $this->_whereableTraitConstruct();
        $this->setTable($table);
        $this->rowsInArray = [];
    }

    public static function createFiltered($table, $filter = [])
    {
        $query = new DeleteQuery($table);
        foreach ($filter as $k => $v) {
            $query->where()->equals($k, $v);
        }
        return $query;
    }

    public function setTable($table)
    {
        if (!$table instanceof Table) {
            $table = Table::fromExpression($table);
        }
        $this->table = $table;
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function rowsIn(string $table)
    {
        $this->rowsInArray[] = $table;
    }

    public function getRowsIn()
    {
        return $this->rowsInArray;
    }
}
