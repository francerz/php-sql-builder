<?php

namespace Francerz\SqlBuilder\Expressions;

interface OneOperandInterface
{
    public function getOperand();
    public function setOperand($operand);
}
