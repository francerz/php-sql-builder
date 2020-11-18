<?php

namespace Francerz\SqlBuilder\Nesting;

interface ValueProxyResolverInterface
{
    public function resolve(): bool;
}