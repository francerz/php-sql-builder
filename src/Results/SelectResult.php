<?php

namespace Francerz\SqlBuilder\Results;

use Countable;
use Francerz\SqlBuilder\CompiledQuery;
use Iterator;

class SelectResult extends AbstractResult implements
    Countable,
    Iterator
{
    private $rows;
    
    public function __construct(CompiledQuery $query, array $rows)
    {
        parent::__construct($query, count($rows));
        $this->rows = $rows;
    }

    public function count()
    {
        return count($this->rows);
    }

    public function current()
    {
        return current($this->rows);
    }
    public function key()
    {
        return key($this->rows);
    }
    public function valid()
    {
        return key($this->rows) !== null;
    }
    public function rewind()
    {
        reset($this->rows);
    }
    public function next()
    {
        next($this->rows);
    }

    public function getColumnValues($column)
    {
        return array_unique(array_column($this->rows, $column));
    }
}