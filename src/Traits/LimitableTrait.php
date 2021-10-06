<?php

namespace Francerz\SqlBuilder\Traits;

trait LimitableTrait
{
    protected $limit;
    protected $offset = 0;

    public function limit(int $limit, int $offset = 0)
    {
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }

    public function paginate(int $page, int $pagesize = 500)
    {
        $this->limit = $pagesize;
        $this->offset = $pagesize * $page;
        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }
    public function getLimitOffset(): int
    {
        return $this->offset;
    }
}
