<?php

namespace Francerz\SqlBuilder\Nesting;

use Francerz\SqlBuilder\Expressions\Comparison\ComparisonOperationInterface;
use Francerz\SqlBuilder\Results\SelectResult;

interface NestOperationResolverInterface
{
    public function requiresTransform(): bool;
    public function nestTransform(SelectResult $parentResult): ?ComparisonOperationInterface;
    public function nestResolve(): bool;
}
