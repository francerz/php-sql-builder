<?php

namespace Francerz\SqlBuilder\Traits;

use Francerz\SqlBuilder\Components\Nest;
use Francerz\SqlBuilder\Nesting\NestedSelect;
use Francerz\SqlBuilder\Nesting\NestMode;
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
        $nest = new Nest($alias, $callback, $query, $mode, $className);
        $nest->init();
        $this->nests[] = $nest;
        return $this;
    }

    public function getNests()
    {
        return $this->nests;
    }
}
