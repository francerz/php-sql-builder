<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\Components\Table;

interface QueryInterface
{
    public function getTable() : Table;
}