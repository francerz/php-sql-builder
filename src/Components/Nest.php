<?php

namespace Francerz\SqlBuilder\Components;

use Francerz\SqlBuilder\Nesting\NestedSelect;
use Francerz\SqlBuilder\Nesting\RowProxy;

class Nest
{
    private $alias, $nested, $callback, $rowProxy;

    public function __construct(string $alias, callable $callback, ?NestedSelect $nested = null)
    {
        $this->alias = $alias;
        $this->callback = $callback;
        $this->nested = $nested;
    }

    public function init()
    {
        $this->rowProxy = new RowProxy();
        call_user_func($this->callback, $this->nested, $this->rowProxy);
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

    public function getRowProxy() : ?RowProxy
    {
        return $this->rowProxy;
    }
}