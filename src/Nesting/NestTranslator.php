<?php

namespace Francerz\SqlBuilder\Nesting;

use Francerz\SqlBuilder\Components\SqlValue;
use Francerz\SqlBuilder\Components\SqlValueArray;
use Francerz\SqlBuilder\Expressions\Comparison\BetweenExpression;
use Francerz\SqlBuilder\Expressions\Comparison\InExpression;
use Francerz\SqlBuilder\Expressions\Comparison\RelationalExpression;
use Francerz\SqlBuilder\Expressions\Comparison\RelationalOperators;
use Francerz\SqlBuilder\Expressions\Logical\ConditionList;
use Francerz\SqlBuilder\Results\SelectResult;
use Francerz\SqlBuilder\SelectQuery;

class NestTranslator
{
    public function translate(SelectQuery $nestedQuery, SelectResult $parentResult) : SelectQuery
    {
        $newQuery = clone $nestedQuery;

        $this->translateConditionList($newQuery->where(), $parentResult);

        return $newQuery;
    }

    private function translateConditionList(ConditionList $list, SelectResult $parentResult)
    {
        foreach($list as $k => $cond) {
            $cnd = $cond->getCondition();
            if ($cnd instanceof ConditionList) {
                $this->translateConditionList($cnd, $parentResult);
                continue;
            } elseif ($cnd instanceof RelationalExpression) {
                if (in_array($cnd->getOperator(), [RelationalOperators::EQUALS, RelationalOperators::NOT_EQUALS])) {
                    $op1 = $cnd->getOperand1();
                    $op2 = $cnd->getOperand2();
                    if ($op2 instanceof ValueProxy) {
                        $cond->setCondition(new InExpression(
                            $cnd->getOperand1(),
                            $this->valueProxyToArray($op2, $parentResult),
                            $cnd->getOperator() === RelationalOperators::NOT_EQUALS
                        ));
                        continue;
                    }
                    if ($op1 instanceof ValueProxy) {
                        $cond->setCondition(new InExpression(
                            $cnd->getOperand2(),
                            $this->valueProxyToArray($op1, $parentResult),
                            $cnd->getOperator() === RelationalOperators::NOT_EQUALS
                        ));
                        continue;
                    }
                } elseif (in_array($cnd->getOperator(), [RelationalOperators::LESS, RelationalOperators::LESS_EQUALS])) {
                    $op1 = $cnd->getOperand1();
                    $op2 = $cnd->getOperand2();
                    if ($op1 instanceof ValueProxy) {
                        $op1 = $this->valueProxyToMin($op1, $parentResult);
                    }
                    if ($op2 instanceof ValueProxy) {
                        $op2 = $this->valueProxyToMax($op2, $parentResult);
                    }
                    $cnd->setOperand1($op1);
                    $cnd->setOperand2($op2);
                    continue;
                } elseif (in_array($cnd->getOperator(), [RelationalOperators::GREATER, RelationalOperators::GREATER_EQUALS])) {
                    $op1 = $cnd->getOperand1();
                    $op2 = $cnd->getOperand2();
                    if ($op1 instanceof ValueProxy) {
                        $op1 = $this->valueProxyToMax($op1, $parentResult);
                    }
                    if ($op2 instanceof ValueProxy) {
                        $op2 = $this->valueProxyToMin($op2, $parentResult);
                    }
                    $cnd->setOperand1($op1);
                    $cnd->setOperand2($op2);
                    continue;
                }
            } elseif ($cnd instanceof BetweenExpression) {
                $opMin = $cnd->getOperand2();
                $opMax = $cnd->getOperand3();
                if ($opMin instanceof ValueProxy && $opMax instanceof ValueProxy) {
                    $opMin = $this->valueProxyToMin($opMin, $parentResult);
                    $opMax = $this->valueProxyToMax($opMax, $parentResult);
                    if (!$cnd->isNegated()) {
                        $cnd->setOperand2($opMin);
                        $cnd->setOperand3($opMax);
                        continue;
                    }
                }
            }
            unset($list[$k]);
        }
    }

    private function valueProxyToArray(ValueProxy $proxy, SelectResult $parentResult) : SqlValueArray
    {
        return new SqlValueArray($parentResult->getColumnValues($proxy->getName()));
    }

    private function valueProxyToMin(ValueProxy $proxy, SelectResult $parentResult) : SqlValue
    {
        return new SqlValue(min($parentResult->getColumnValues($proxy->getName())));
    }

    private function valueProxyToMax(ValueProxy $proxy, SelectResult $parentResult) : SqlValue
    {
        return new SqlValue(max($parentResult->getColumnValues($proxy->getName())));
    }

    private function valueProxyToMergedArray(ValueProxy $proxy, SelectResult $parentResult) : SqlValueArray
    {
        $array = [];
        $arrays = $parentResult->getColumnValues($proxy->getName());
        foreach ($arrays as $a) {
            foreach ($a as $v) {
                $array[] = $v;
            }
        }
        return new SqlValueArray($array);
    }
}