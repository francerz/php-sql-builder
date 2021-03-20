<?php

namespace Francerz\SqlBuilder\Results;

use ArrayAccess;
use Countable;
use Exception;
use Francerz\SqlBuilder\CompiledQuery;
use Iterator;
use JsonSerializable;
use LogicException;

class SelectResult extends AbstractResult implements
    Countable,
    Iterator,
    ArrayAccess,
    JsonSerializable
{
    private $rows;
    
    public function __construct(CompiledQuery $query, array $rows, bool $success = true)
    {
        parent::__construct($query, count($rows), $success);
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

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->rows);
    }

    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->rows[$offset];
        }
    }

    public function offsetSet($offset, $value)
    {
        throw new LogicException("Read Only access.");
    }

    public function offsetUnset($offset)
    {
        throw new LogicException('Read Only access.');
    }

    public function first()
    {
        $first = reset($this->rows);
        if ($first !== false) {
            return $first;
        }
    }

    public function last()
    {
        $last = end($this->rows);
        if ($last !== false) {
            return $last;
        }
    }

    public function toArray()
    {
        return $this->rows;
    }

    public function getColumnValues($column, bool $unique = true)
    {
        $values = array_column($this->rows, $column);
        if ($unique) {
            $values = array_unique($values);
        }
        return $values;
    }

    public function jsonSerialize()
    {
        return $this->rows;
    }
}