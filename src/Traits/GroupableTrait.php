<?php

namespace Francerz\SqlBuilder\Traits;

use Francerz\SqlBuilder\Components\Column;
use Francerz\SqlBuilder\Expressions\ComparableComponentInterface;
use Francerz\SqlBuilder\Expressions\Logical\ConditionList;
use InvalidArgumentException;

trait GroupableTrait
{
    protected $groupBy = [];
    protected $having;

    public function __construct()
    {
        $this->having = new ConditionList();
    }

    public function groupBy($group)
    {
        if (is_string($group)) {
            $group = Column::fromString($group);
        }
        if (!$group instanceof ComparableComponentInterface) {
            throw new InvalidArgumentException('Invalid groupBy component');
        }
        $this->groupBy[] = $group;
        return $this;
    }

    public function getGroupBy() : array
    {
        return $this->groupBy;
    }

    public function having() : ConditionList
    {
        $having = $this->having;
        $args = func_get_args();
        if (!empty($args)) {
            call_user_func_array($having, $args);
        }
        return $having;
    }

    protected function __clone()
    {
        $this->having = clone $this->having;
    }
}