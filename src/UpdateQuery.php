<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\Components\SqlValue;
use Francerz\SqlBuilder\Components\Table;
use Francerz\SqlBuilder\Expressions\ComparableComponentInterface;
use Francerz\SqlBuilder\Traits\JoinableTrait;
use Francerz\SqlBuilder\Traits\WhereableTrait;

class UpdateQuery implements QueryInterface
{
    use JoinableTrait, WhereableTrait {
        WhereableTrait::__construct as private _whereableTraitConstruct;
    }
    private $table;
    private $sets;

    public function __construct($table)
    {
        $this->_whereableTraitConstruct();
        $this->setTable($table);
    }

    public function setTable($table)
    {
        $this->table = Table::fromExpression($table);
    }

    public function getTable()
    {
        return $this->table;
    }

    public function set($column, $value)
    {
        if (!$value instanceof ComparableComponentInterface) {
            $value = Query::value($value);
        }
        $this->sets[$column] = $value;
    }

    public function getSets()
    {
        return $this->sets;
    }
}