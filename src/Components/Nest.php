<?php

namespace Francerz\SqlBuilder\Components;

use Francerz\SqlBuilder\SelectQuery;

class Nest
{
    private $alias, $query, $callback;

    public function __construct(string $alias, callable $callback, ?SelectQuery $query = null)
    {
        $this->alias = $alias;
        $this->callback = $callback;
        $this->query = $query;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function getQuery()
    {
        return $this->query;
    }
}