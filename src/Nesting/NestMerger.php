<?php

namespace Francerz\SqlBuilder\Nesting;

use Francerz\PowerData\Arrays;
use Francerz\PowerData\Index;
use Francerz\SqlBuilder\Components\Column;
use Francerz\SqlBuilder\Components\Nest;
use Francerz\SqlBuilder\Expressions\BooleanResultInterface;
use Francerz\SqlBuilder\Expressions\Comparison\RelationalExpression;
use Francerz\SqlBuilder\Expressions\Comparison\RelationalOperators;
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
        $mode = $nest->getMode();
        $className = $nest->getClassName();

        $query = static::getMatches($query, $matches);
        $childIndex = new Index($children->toArray($className), $matches);
        // $parentIndex= new Index($parents->toArray(), array_keys($matches));

        foreach ($parents as $parent) {
            $childs = [];
            $parentRow->setCurrent($parent);
            $subchildren = static::findMatchedChildren($childIndex, $parent, $matches);
            foreach ($subchildren as $child) {
                $childRow->setCurrent($child);
                if (
                    $this->mergeConditionList($query->where()) &&
                    $this->mergeConditionList($query->having())
                ) {
                    $childs[] = $child;
                    if ($mode->is(NestMode::SINGLE_FIRST)) {
                        break;
                    }
                }
            }
            if ($mode->is(NestMode::SINGLE_FIRST)) {
                $childs = empty($childs) ? null : reset($childs);
            } elseif ($mode->is(NestMode::SINGLE_LAST)) {
                $childs = empty($childs) ? null : end($childs);
            }
            $parent->$alias = $childs;
        }
    }

    public function placeholdSelect(SelectQuery $query, RowProxy $rowProxy)
    {
        $query = clone $query;
        $query->setWhere($this->placeholdConditionList($query->where(), $rowProxy));
        $query->setHaving($this->placeholdConditionList($query->having(), $rowProxy));
        return $query;
    }

    public function placeholdConditionList(ConditionList $conditions, RowProxy $rowProxy)
    {
        $newConds = new ConditionList();
        foreach ($conditions as $cond) {
            $cnd = $cond->getCondition();
            if ($cnd instanceof ConditionList) {
                $this->placeholdConditionList($cnd, $rowProxy);
            } elseif ($cnd instanceof OneOperandInterface) {
                $op = $this->placeholdOperand($cnd->getOperand(), $rowProxy);
                if (!isset($op)) {
                    continue;
                }
                $cnd->setOperand($op);
            } elseif ($cnd instanceof TwoOperandsInterface) {
                $op1 = $this->placeholdOperand($cnd->getOperand1(), $rowProxy);
                $op2 = $this->placeholdOperand($cnd->getOperand2(), $rowProxy);
                if (!isset($op1, $op2)) {
                    continue;
                }
                $cnd->setOperand1($op1);
                $cnd->setOperand2($op2);
            } elseif ($cnd instanceof ThreeOperandsInterface) {
                $op1 = $this->placeholdOperand($cnd->getOperand1(), $rowProxy);
                $op2 = $this->placeholdOperand($cnd->getOperand2(), $rowProxy);
                $op3 = $this->placeholdOperand($cnd->getOperand3(), $rowProxy);
                if (!isset($op1, $op2, $op3)) {
                    continue;
                }
                $cnd->setOperand1($op1);
                $cnd->setOperand2($op2);
                $cnd->setOperand3($op3);
            }
            $newConds->add($cond);
        }
        return $newConds;
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

    public function mergeConditionList(ConditionList $conditions): bool
    {
        $result = true;
        foreach ($conditions as $cond) {
            $cnd = $cond->getCondition();
            $res = $this->handleBooleanResult($cnd);
            if ($cnd instanceof NegatableInterface && $cnd->isNegated()) {
                $res = !$res;
            }
            switch ($cond->getConnector()) {
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

    public function handleBooleanResult(BooleanResultInterface $bool): bool
    {
        if ($bool instanceof ConditionList) {
            return $this->mergeConditionList($bool);
        } elseif ($bool instanceof NestOperationResolverInterface) {
            return $bool->nestResolve();
        }
        return false;
    }

    private static function getMatchesFromConditionList(ConditionList $conds, ?array &$matches = []): ConditionList
    {
        $newConds = new ConditionList();
        foreach ($conds as $cond) {
            if ($cond->getConnector() == LogicConnectors::AND) {
                $cnd = $cond->getCondition();
                if ($cnd instanceof RelationalExpression && $cnd->getOperator()->is(RelationalOperators::EQUALS)) {
                    $op1 = $cnd->getOperand1();
                    $op2 = $cnd->getOperand2();
                    if ($op1 instanceof ValueProxy && $op2 instanceof ValueProxy) {
                        $matches[$op2->getName()] = $op1->getName();
                        continue;
                    }
                }
            }
            $newConds->add($cond);
        }
        return $newConds;
    }

    private static function getMatches(SelectQuery $query, ?array &$matches = []): SelectQuery
    {
        if (is_null($matches)) {
            $matches = [];
        }
        $query->setWhere(static::getMatchesFromConditionList($query->where(), $matches));
        $query->setHaving(static::getMatchesFromConditionList($query->having(), $matches));
        return $query;
    }

    private static function findMatchedChildren(Index $index, object $parent, array $matches)
    {
        return $index->findAll(Arrays::replaceKeys((array)$parent, $matches));
    }
}
