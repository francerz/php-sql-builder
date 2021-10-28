<?php

namespace Francerz\SqlBuilder\Components;

use Francerz\SqlBuilder\SelectQuery;
use InvalidArgumentException;
use LogicException;

class Table
{
    private $source;
    private $alias;
    private $database;

    public function __construct($source, ?string $alias = null, ?string $database = null)
    {
        $this->source = $source;
        $this->alias = $alias;
        $this->database = $database;
    }

    public static function fromExpression($expression): Table
    {
        if ($expression instanceof Table) {
            return $expression;
        } elseif (is_array($expression)) {
            return static::fromArray($expression);
        } elseif (is_string($expression)) {
            $args = func_get_args();
            if (count($args) === 1) {
                return static::fromString($expression);
            }
            if (is_callable($args[1])) {
                return static::fromCallable($expression, $args[1]);
            }
            if ($args[1] instanceof SelectQuery) {
                return static::fromQuery($expression, $args[1]);
            }
        }
        throw new InvalidArgumentException();
    }

    private static function fromCallable(string $alias, callable $callable)
    {
        $query = new SelectQuery();
        call_user_func($callable, $query);
        return static::fromQuery($alias, $query);
    }

    private static function fromQuery(string $alias, SelectQuery $query)
    {
        return new static($query, $alias);
    }

    private static function fromString(string $string)
    {
        if (stripos($string, ' AS ', 1) === false) {
            return new static($string);
        }
        list($table, $alias) = preg_split('/\s+AS\s+/i', $string);
        $db = null;
        if (strpos($table, '.') !== false) {
            list($db, $table) = explode('.', $table, 2);
        }
        return new static($table, $alias, $db);
    }

    private static function fromArray(array $array)
    {
        if (count($array) !== 1) {
            throw new InvalidArgumentException();
        }
        $db = null;
        $alias = key($array);
        $alias = is_string($alias) ? $alias : null;
        $source = current($array);
        if (is_callable($source) && is_string($alias)) {
            return static::fromCallable($alias, $source);
        } elseif (is_string($source) && strpos($source, '.') !== false) {
            list($db, $source) = explode('.', $source, 2);
        }
        return new static($source, $alias, $db);
    }

    /**
     * @param string|SelectQuery $source
     * @return void
     */
    public function setSource($source)
    {
        if (!is_string($source) && !$source instanceof SelectQuery) {
            throw new InvalidArgumentException('Table source must be String or SelectQuery');
        }
        $this->source = $source;
    }

    /**
     * @return string|SelectQuery
     */
    public function getSource()
    {
        return $this->source;
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
        if (is_string($this->source)) {
            return $this->source;
        }
        throw new LogicException('Not alias or name found in table.');
    }

    public function getDatabase()
    {
        return $this->database;
    }
}
