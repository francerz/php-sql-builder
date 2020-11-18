<?php

namespace Francerz\SqlBuilder\Nesting;

use Francerz\SqlBuilder\Components\Column;
use Francerz\SqlBuilder\Components\Nest;
use Francerz\SqlBuilder\Expressions\BooleanResultInterface;
use Francerz\SqlBuilder\Expressions\Logical\ConditionList;
use Francerz\SqlBuilder\Expressions\Logical\LogicConnectors;
use Francerz\SqlBuilder\Expressions\NegatableInterface;
use Francerz\SqlBuilder\Expressions\OneOperandInterface;
use Francerz\SqlBuilder\Expressions\ThreeOperandsInterface;
use Francerz\SqlBuilder\Expressions\TwoOperandsInterface;
use Francerz\SqlBuilder\Results\SelectResult;
use Francerz\SqlBuilder\SelectQuery;

class NestMerger
{
    public function merge(SelectResult $parents, SelectResult $children, Nest $nest)
    {
        $parentRow = $nest->getRowProxy();
        $childRow = new RowProxy();
        $alias = $nest->getAlias();
        $query = $nest->getNested()->getSelect();
        $query = $this->placeholdSelect($query, $childRow);

        foreach($parents as $parent) {
            $childs = [];
            $parentRow->setCurrent($parent);
            foreach ($children as $child) {
                $childRow->setCurrent($child);
                if ($this->mergeConditionList($query->where()) &&
                    $this->mergeConditionList($query->having())
                ) {
                    $childs[] = $child;
                }
            }
            $parent->$alias = $childs;
        }
    }

    public function placeholdSelect(SelectQuery $query, RowProxy $rowProxy)
    {
        $query = clone $query;
        $this->placeholdConditionList($query->where(), $rowProxy);
        return $query;
    }

    public function placeholdConditionList(ConditionList $conditions, RowProxy $rowProxy)
    {
        foreach ($conditions as $cond) {
            $cnd = $cond->getCondition();
            if ($cnd instanceof ConditionList) {
                $this->placeholdConditionList($cnd, $rowProxy);
            } elseif ($cnd instanceof OneOperandInterface) {
                $op = $this->placeholdOperand($cnd->getOperand(), $rowProxy);
                $cnd->setOperand($op);
            } elseif ($cnd instanceof TwoOperandsInterface) {
                $op1 = $this->placeholdOperand($cnd->getOperand1(), $rowProxy);
                $op2 = $this->placeholdOperand($cnd->getOperand2(), $rowProxy);
                $cnd->setOperand1($op1);
                $cnd->setOperand2($op2);
            } elseif ($cnd instanceof ThreeOperandsInterface) {
                $op1 = $this->placeholdOperand($cnd->getOperand1(), $rowProxy);
                $op2 = $this->placeholdOperand($cnd->getOperand2(), $rowProxy);
                $op3 = $this->placeholdOperand($cnd->getOperand3(), $rowProxy);
                $cnd->setOperand1($op1);
                $cnd->setOperand2($op2);
                $cnd->setOperand3($op3);
            }
        }
    }

    public function placeholdOperand($operand, RowProxy $rowProxy)
    {
        if ($operand instanceof ValueProxy) {
            return $operand;
        }
        if ($operand instanceof Column) {
            return new ValueProxy($rowProxy, $operand->getAliasOrName());
        }
    }

    public function mergeConditionList(ConditionList $conditions) : bool
    {
        $result = true;
        foreach ($conditions as $cond)
        {
            $cnd = $cond->getCondition();
            $res = $this->handleBooleanResult($cnd);
            if ($cnd instanceof NegatableInterface && $cnd->isNegated()) {
                $res = !$res;
            }
            switch($cond->getConnector()) {
                case LogicConnectors::AND:
                    $result = $result && $res;
                    break;
                case LogicConnectors::OR:
                    $result = $result || $res;
                    break;
            }
        }
        return $result;
    }

    public function handleBooleanResult(BooleanResultInterface $bool) : bool
    {
        if ($bool instanceof ConditionList) {
            return $this->mergeConditionList($bool);
        } elseif ($bool instanceof ValueProxyResolverInterface) {
            return $bool->resolve();
        }
        return false;
    }
}