<?php

namespace Francerz\SqlBuilder\Traits;

use Francerz\SqlBuilder\Components\Nest;
use Francerz\SqlBuilder\Nesting\NestedSelect;
use Francerz\SqlBuilder\Nesting\NestMode;
use Francerz\SqlBuilder\Nesting\RowProxy;
use Francerz\SqlBuilder\SelectQuery;
use stdClass;

trait NestableTrait
{
    protected $nests;

    public function __construct()
    {
        $this->nests = [];
    }

    /**
     * Nests a select which iterates over the results of current query.
     *
     * @param string|array $alias Nest result alias name. Or array like [$alias => $query]
     * @param callable $callback Iterator compare function with parameters (NestedSelect $select, RowProxy $row)
     * @param NestMode::value $mode Nest iteration mode will be Collection, First or Last element.
     * @param string $className Class to cast result objects. Defaults to stdClass.
     * @return void
     */
    public function nest($alias, callable $callback, $mode = NestMode::COLLECTION, string $className = stdClass::class)
    {
        $query = null;
        if (is_array($alias)) {
            $query = current($alias);
            $alias = key($alias);
        }
        if (is_callable($query)) {
            $cbQuery = $query;
            $query = new SelectQuery();
            call_user_func($cbQuery, $query);
        }
        if (!$query instanceof NestedSelect) {
            $query = new NestedSelect($query);
        }
        $nest = new Nest($alias, $query, $mode, $className);
        call_user_func($callback, $query, $nest->getRowProxy());
        $this->nests[] = $nest;
        return $this;
    }

    public function getNests()
    {
        return $this->nests;
    }

    public function nestMany(string $alias, SelectQuery $query, ?RowProxy &$row, string $className = stdClass::class)
    {
        $nest = new Nest($alias, new NestedSelect($query), NestMode::COLLECTION, $className);
        $row = $nest->getRowProxy();
        $this->nests[] = $nest;
        return $nest;
    }

    public function linkFirst(string $alias, SelectQuery $query, ?RowProxy &$row, string $className = stdClass::class)
    {
        $nest = new Nest($alias, new NestedSelect($query), NestMode::SINGLE_FIRST, $className);
        $row = $nest->getRowProxy();
        $this->nests[] = $nest;
        return $nest;
    }

    public function linkLast(string $alias, SelectQuery $query, ?RowProxy &$row, string $className = stdClass::class)
    {
        $nest = new Nest($alias, new NestedSelect($query), NestMode::SINGLE_LAST, $className);
        $row = $nest->getRowProxy();
        $this->nests[] = $nest;
        return $nest;
    }
}
