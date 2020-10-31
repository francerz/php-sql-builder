<?php

namespace Francerz\SqlBuilder\Components;

use Francerz\SqlBuilder\Expressions\ComparableComponentInterface;
use Francerz\SqlBuilder\SelectQuery;
use InvalidArgumentException;
use SNMP;

class Column implements ComparableComponentInterface
{
    private $column;
    private $table;
    private $alias;

    public function __construct($column, ?string $alias = null, ?string $table = null)
    {
        $this->column = $column;
        $this->alias = $alias;
        $this->table = $table;
    }

    private static function getColTable(string $string, &$column, &$table)
    {
        $dotPos = strripos($string, '.', 1);
        if ($dotPos !== false) {
            $table = trim(substr($string, 0, $dotPos));
            $string = substr($string, $dotPos + 1);
        }
        $column = $string;
    }

    public static function fromString(string $string)
    {
        $alias = null;
        $table = null;
        $column = '';
        $asLastPos = strripos($string, ' AS ', 1);
        if ($asLastPos !== false) {
            $alias = trim(substr($string, $asLastPos + 4));
            $string = substr($string, 0, $asLastPos);
        }

        static::getColTable($string, $column, $table);

        return new static($column, $alias, $table);
    }
    public static function fromArray(array $array, ?string $table = null) : array
    {
        $arr = [];

        foreach ($array as $k => $item) {
            $alias = is_string($k) ? $k : null;
            if (is_string($item)) {
                static::getColTable($item, $column, $table);
                $item = new Column($column, $alias, $table);
            } elseif ($item instanceof SelectQuery) {
                if ($item->getLimit() !== 1 || count($item->getAllColumns()) !== 1) {
                    throw new InvalidArgumentException(
                        'Column source SelectQuery MUST have only one column and limit 1.'
                    );
                }
                $item = new Column($item, $alias);
            }
            if (!$item instanceof Column) {
                continue;
            }
            if (isset($table)) {
                $item->setTable($table);
            }
            $arr[] = $item;
        }

        return $arr;
    }

    public function getColumn()
    {
        return $this->column;
    }
    public function setTable(string $table)
    {
        $this->table = $table;
    }
    public function getTable()
    {
        return $this->table;
    }
    public function getAlias()
    {
        return $this->alias;
    }
}