<?php

namespace Francerz\SqlBuilder\Components;

use Francerz\SqlBuilder\Expressions\Logical\ConditionList;
use Francerz\SqlBuilder\Nesting\NestedSelect;
use Francerz\SqlBuilder\Nesting\NestMode;
use Francerz\SqlBuilder\Nesting\RowProxy;
use Francerz\SqlBuilder\SelectQuery;
use stdClass;

class Nest
{
    private $alias;
    private $nested;
    private $where;
    private $rowProxy;
    private $mode;
    private $className;

    public function __construct(
        string $alias,
        NestedSelect $nested,
        $mode = NestMode::COLLECTION,
        string $className = stdClass::class
    ) {
        $this->rowProxy = new RowProxy();
        $this->alias = $alias;
        $this->nested = $nested;
        $this->mode = NestMode::coerce($mode);
        $this->className = $className;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getNested()
    {
        return $this->nested;
    }

    public function getRowProxy(): ?RowProxy
    {
        return $this->rowProxy;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function where()
    {
        $args = func_get_args();
        return call_user_func_array([$this->nested->getSelect(), 'where'], $args);
    }
}
