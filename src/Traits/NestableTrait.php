<?php

namespace Francerz\SqlBuilder\Traits;

use Francerz\SqlBuilder\Components\Nest;
use Francerz\SqlBuilder\SelectQuery;

trait NestableTrait
{
    private $nests;

    public function __construct()
    {
        $this->nests = [];
    }

    public function nest($alias, callable $callback)
    {
        if (is_array($alias)) {
            $query = current($alias);
            $alias = key($alias);
        }
        if (!$query instanceof SelectQuery) {
            $query = new SelectQuery();
        }
        $this->nests[] = new Nest($alias, $callback, $query);
    }

    public function getNests()
    {
        return $this->nests;
    }
}