<?php

namespace Francerz\SqlBuilder\Expressions;

interface ThreeOperandsInterface
{
    public function getOperand1();
    public function setOperand1($operand);
    public function getOperand2();
    public function setOperand2($operand);
    public function getOperand3();
    public function setOperand3($operand);
}
