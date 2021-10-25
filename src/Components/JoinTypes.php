<?php

namespace Francerz\SqlBuilder\Components;

use Francerz\Enum\AbstractEnum;

final class JoinTypes extends AbstractEnum
{
    public const CROSS_JOIN = 'CROSS JOIN';
    public const INNER_JOIN = 'INNER JOIN';
    public const LEFT_JOIN = 'LEFT JOIN';
    public const RIGHT_JOIN = 'RIGHT JOIN';
    public const LEFT_OUTER_JOIN = 'LEFT OUTER JOIN';
    public const RIGHT_OUTER_JOIN = 'RIGHT OUTER JOIN';
    public const FULL_OUTER_JOIN = 'FULL OUTER JOIN';
}
