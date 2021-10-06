<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\Components\Column;
use Francerz\SqlBuilder\Components\Set;
use Francerz\SqlBuilder\Components\Table;
use Francerz\SqlBuilder\Expressions\ComparableComponentInterface;
use Francerz\SqlBuilder\Traits\JoinableTrait;
use Francerz\SqlBuilder\Traits\LimitableInterface;
use Francerz\SqlBuilder\Traits\LimitableTrait;
use Francerz\SqlBuilder\Traits\SortableInterface;
use Francerz\SqlBuilder\Traits\SortableTrait;
use Francerz\SqlBuilder\Traits\WhereableTrait;

class UpdateQuery implements QueryInterface, LimitableInterface, SortableInterface
{
    use JoinableTrait;
    use WhereableTrait {
        WhereableTrait::__construct as private _whereableTraitConstruct;
    }
    use LimitableTrait;
    use SortableTrait;

    private $table;
    private $sets;
    private $matches = [];

    public function __construct($table)
    {
        $this->_whereableTraitConstruct();
        $this->setTable($table);
    }

    public static function createUpdate($table, $data = null, array $matching = [], array $columns = [])
    {
        $query = new UpdateQuery($table);
        if (empty($data)) {
            return $query;
        }
        if (is_object($data)) {
            $data = (array)$data;
        }
        foreach ($data as $k => $v) {
            if (in_array($k, $matching)) {
                $key = new Column($k, null, $query->getTable()->getAliasOrName());
                $query->where()->equals($key, $v);
                $query->matches[$k] = $v;
            }
            if (empty($columns)) {
                $query->set($k, $v);
                continue;
            }
            if (in_array($k, $columns)) {
                $query->set($k, $v);
            }
        }

        return $query;
    }

    public function setTable($table)
    {
        $this->table = Table::fromExpression($table);
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function set($column, $value)
    {
        if (!$value instanceof ComparableComponentInterface) {
            $value = Query::value($value);
        }
        if (!$column instanceof Column) {
            $column = new Column($column, null, $this->table->getAliasOrName());
        }
        $this->sets[] = new Set($column, $value);
    }

    public function getSets()
    {
        return $this->sets;
    }

    public function getMatches()
    {
        return $this->matches;
    }
}
