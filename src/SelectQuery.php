<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\Components\Column;
use Francerz\SqlBuilder\Components\Table;
use Francerz\SqlBuilder\Components\TableReference;
use Francerz\SqlBuilder\Traits\GroupableTrait;
use Francerz\SqlBuilder\Traits\JoinableTrait;
use Francerz\SqlBuilder\Traits\LimitableTrait;
use Francerz\SqlBuilder\Traits\NestableTrait;
use Francerz\SqlBuilder\Traits\WhereableTrait;

class SelectQuery implements QueryInterface
{
    use JoinableTrait, WhereableTrait, NestableTrait, GroupableTrait, LimitableTrait {
        WhereableTrait::__construct as private Whereable__construct;
        GroupableTrait::__construct as private Groupable__construct;
    }

    private $from;
    private $columns;

    public function __construct($table = null, ?array $columns = null)
    {
        $this->Whereable__construct();
        $this->Groupable__construct();
        if (isset($table)) {
            $this->from($table, $columns);
        }
    }

    public function from($table, ?array $columns = null)
    {
        if (!$table instanceof Table) {
            $table = Table::fromExpression($table);
        }
        $this->from = new TableReference($table, $columns);
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function getTable() : Table
    {
        return $this->from->getTable();
    }

    public function getAllColumns()
    {
        $columns = $this->from->getColumns();
        $joins = $this->getJoins();
        foreach ($joins as $join) {
            $columns = array_merge($columns, $join->getTableReference()->getColumns());
        }
        return $columns;
    }
}