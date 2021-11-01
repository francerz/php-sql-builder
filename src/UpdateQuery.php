<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\Components\Column;
use Francerz\SqlBuilder\Components\Set;
use Francerz\SqlBuilder\Components\Table;
use Francerz\SqlBuilder\Expressions\ComparableComponentInterface;
use Francerz\SqlBuilder\Helpers\ModelHelper;
use Francerz\SqlBuilder\Traits\JoinableTrait;
use Francerz\SqlBuilder\Traits\LimitableInterface;
use Francerz\SqlBuilder\Traits\LimitableTrait;
use Francerz\SqlBuilder\Traits\SortableInterface;
use Francerz\SqlBuilder\Traits\SortableTrait;
use Francerz\SqlBuilder\Traits\WhereableTrait;
use ReflectionProperty;

class UpdateQuery implements QueryInterface, LimitableInterface, SortableInterface
{
    use JoinableTrait;
    use WhereableTrait {
        WhereableTrait::__construct as private _whereableTraitConstruct;
    }
    use LimitableTrait;
    use SortableTrait;

    private $connection = null;
    private $table;
    private $sets;

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
        if (empty($matching) && empty($columns)) {
            $matching = array_map(function (ReflectionProperty $prop) {
                return $prop->getName();
            }, ModelHelper::getDataProperties($data, ModelHelper::PROPERTY_SKIP_DEFAULT));
            $columns = array_map(function (ReflectionProperty $prop) {
                return $prop->getName();
            }, ModelHelper::getDataProperties($data, ModelHelper::PROPERTY_SKIP_KEY));
        }
        $data = ModelHelper::dataAsArray($data);
        foreach ($data as $k => $v) {
            if (in_array($k, $matching)) {
                $key = new Column($k, null, $query->getTable()->getAliasOrName());
                $query->where()->equals($key, $v);
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

    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    public function getConnection()
    {
        return $this->connection;
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
}
