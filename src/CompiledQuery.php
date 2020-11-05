<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\QueryInterface;

class CompiledQuery
{
    private $query;
    private $values;
    private $queryObj;

    public function __construct(string $query, array $values = [], ?QueryInterface $queryObj = null)
    {
        $this->query = $query;
        $this->values = $values;
        $this->queryObj = $queryObj;
    }

    public function getQuery() : string
    {
        return $this->query;
    }

    public function getValues() : array
    {
        return $this->values;
    }

    public function getObject()
    {
        return $this->queryObj;
    }
}