<?php

namespace Francerz\SqlBuilder\Expressions;

interface TwoOperandsInterface
{
    public function getOperand1();
    public function setOperand1($operand);
    public function getOperand2();
    public function setOperand2($operand);
}