<?php

namespace Francerz\SqlBuilder\Components;

use Francerz\SqlBuilder\Nesting\NestedSelect;

class Nest
{
    private $alias, $nested, $callback;

    public function __construct(string $alias, callable $callback, ?NestedSelect $nested = null)
    {
        $this->alias = $alias;
        $this->callback = $callback;
        $this->nested = $nested;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function getNested()
    {
        return $this->nested;
    }
}