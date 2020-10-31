<?php

namespace Francerz\SqlBuilder\Components;

class TableReference
{
    private $table;
    private $columns = null;

    public function __construct(Table $table, ?array $columns = null)
    {
        $this->table = $table;
        if (is_array($columns)) {
            $this->addColumns($columns);
        }
    }

    public function addColumns(array $columns)
    {
        if (is_null($this->columns)) {
            $this->columns = [];
        }
        $columns = Column::fromArray($columns, $this->table->getAliasOrName());
        $this->columns = array_merge($this->columns, $columns);
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getColumns()
    {
        if (is_null($this->columns)) {
            return [new Column('*', null, $this->table->getAliasOrName())];
        }
        return $this->columns;
    }
}