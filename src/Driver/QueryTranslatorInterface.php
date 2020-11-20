<?php

namespace Francerz\SqlBuilder\Driver;

use Francerz\SqlBuilder\QueryInterface;

interface QueryTranslatorInterface
{
    public function translateQuery(QueryInterface $query) : QueryInterface;
}