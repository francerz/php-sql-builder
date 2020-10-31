<?php

namespace Francerz\SqlBuilder\Components;

interface JoinTypes
{
    const CROSS_JOIN = 'CROSS JOIN';
    const INNER_JOIN = 'INNER JOIN';
    const LEFT_JOIN = 'LEFT JOIN';
    const RIGHT_JOIN = 'RIGHT JOIN';
    const LEFT_OUTER_JOIN = 'LEFT OUTER JOIN';
    const RIGHT_OUTER_JOIN = 'RIGHT OUTER JOIN';
    const FULL_OUTER_JOIN = 'FULL OUTER JOIN';
}