<?php

namespace Francerz\SqlBuilder\Traits;

Trait LimitableTrait
{
    private $limit;
    private $offset = 0;

    public function limit(int $limit, int $offset = 0)
    {
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }
    public function getLimitOffset() : int
    {
        return $this->offset;
    }
}