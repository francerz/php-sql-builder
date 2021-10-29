<?php

namespace Francerz\SqlBuilder\Components;

use Francerz\SqlBuilder\Expressions\ComparableComponentInterface;
use Francerz\SqlBuilder\SelectQuery;
use InvalidArgumentException;

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

        if (preg_match('/^([\w]+.)?[\w]+$/', $string)) {
            static::getColTable($string, $column, $table);
        } elseif (false && preg_match('/^([\w]+)\(([^()]+|(?1))*\)$/', $string, $matches)) {
            $column = new SqlFunction($matches[1], [$matches[2]]);
        } else {
            $column = new SqlRaw($string);
        }

        return new static($column, $alias, $table);
    }

    public static function fromExpression($content, ?string $alias = null, ?string $table = null)
    {
        $alias = is_string($alias) && !empty($alias) ? $alias : null;

        if (is_string($content)) {
            if (is_string($table)) {
                static::getColTable($content, $column, $table);
                return new Column($column, $alias, $table);
            }
            $column = static::fromString($content);
            $column->alias = $column->alias ?? $alias;
            $column->table = $column->table ?? $table;
            return $column;
        } elseif (is_array($content)) {
            $v = reset($content);
            $k = key($content);
            return static::fromExpression($v, $k, $table);
        } elseif ($content instanceof SelectQuery) {
            if ($content->getLimit() !== 1 || count($content->getAllColumns()) !== 1) {
                throw new InvalidArgumentException(
                    'Column source SelectQuery MUST have only one column and limit 1.'
                );
            }
            return new Column($content, $alias, $table);
        } elseif ($content instanceof SqlRaw) {
            return new Column($content, $alias);
        } elseif ($content instanceof SqlFunction) {
            return new Column($content, $alias);
        } else {
            return new Column(new SqlRaw((string)$content), $alias, $table);
        }
    }

    public static function fromArray(array $array, ?string $table = null): array
    {
        $arr = [];
        foreach ($array as $k => $item) {
            $arr[] = static::fromExpression($item, $k, $table);
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
    public function getAliasOrName()
    {
        if (isset($this->alias)) {
            return $this->alias;
        }
        return $this->column;
    }
}
