<?php

namespace Francerz\SqlBuilder\Traits;

interface LimitableInterface
{
    public function limit(int $limit, int $offset = 0);
    public function paginate(int $page, int $pagesize = 500);
    public function getLimit(): ?int;
    public function getLimitOffset() : int;
}