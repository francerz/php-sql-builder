<?php

namespace Francerz\SqlBuilder\Results;

use ArrayAccess;
use Countable;
use Francerz\PowerData\Objects;
use InvalidArgumentException;
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

    public function __construct(array $rows, bool $success = true)
    {
        parent::__construct(count($rows), $success);
        $this->rows = $rows;
    }

    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->rows);
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($this->rows);
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->rows);
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return key($this->rows) !== null;
    }

    #[\ReturnTypeWillChange]
    public function rewind()
    {
        reset($this->rows);
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        next($this->rows);
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->rows);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->rows[$offset];
        }
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        throw new LogicException("Read Only access.");
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        throw new LogicException('Read Only access.');
    }

    /**
     * @deprecated v0.5.0
     *
     * @return void
     */
    public function first()
    {
        $first = reset($this->rows);
        if ($first !== false) {
            return $first;
        }
    }

    /**
     * @deprecated v0.5.0
     *
     * @return void
     */
    public function last()
    {
        $last = end($this->rows);
        if ($last !== false) {
            return $last;
        }
    }

    public function toArray(?string $class = null)
    {
        if (!isset($class)) {
            return $this->rows;
        }

        if (!class_exists($class)) {
            throw new InvalidArgumentException("Class '{$class}' doesn't exists.");
        }

        $rows = [];
        foreach ($this->rows as $row) {
            $new = Objects::cast($row, $class);
            array_push($rows, $new);
        }
        return $rows;
    }

    public function getColumnValues($column, bool $unique = true)
    {
        $values = array_column($this->rows, $column);
        if ($unique) {
            $values = array_unique($values);
        }
        return $values;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->rows;
    }
}
