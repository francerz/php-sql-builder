<?php

namespace Francerz\SqlBuilder\Traits;

use Francerz\SqlBuilder\Components\Nest;
use Francerz\SqlBuilder\Nesting\NestedSelect;

trait NestableTrait
{
    protected $nests;

    public function __construct()
    {
        $this->nests = [];
    }

    public function nest($alias, callable $callback)
    {
        $query = null;
        if (is_array($alias)) {
            $query = current($alias);
            $alias = key($alias);
        }
        if (!$query instanceof NestedSelect) {
            $query = new NestedSelect($query);
        }
        $nest = new Nest($alias, $callback, $query);
        $nest->init();
        $this->nests[] = $nest;
        return $this;
    }

    public function getNests()
    {
        return $this->nests;
    }
}