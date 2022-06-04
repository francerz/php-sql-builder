<?php

namespace Francerz\SqlBuilder\Compiles;

abstract class AbstractCompiledStatement
{
    private $query;
    private $values;

    public function __construct(string $query, array $values = [])
    {
        $this->query = $query;
        $this->values = $values;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getValues(): array
    {
        return $this->values;
    }
}
