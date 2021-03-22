<?php

namespace Francerz\SqlBuilder\Nesting;

use Francerz\SqlBuilder\Components\SqlValue;
use Francerz\SqlBuilder\Components\SqlValueArray;
use Francerz\SqlBuilder\Expressions\Logical\ConditionList;
use Francerz\SqlBuilder\Expressions\Logical\LogicConnectors;
use Francerz\SqlBuilder\Results\SelectResult;
use Francerz\SqlBuilder\SelectQuery;

class NestTranslator
{
    public function translate(SelectQuery $nestedQuery, SelectResult $parentResult) : SelectQuery
    {
        $newQuery = clone $nestedQuery;

        $newQuery->setWhere($this->translateConditionList($newQuery->where(), $parentResult));
        $newQuery->setHaving($this->translateConditionList($newQuery->having(), $parentResult));

        return $newQuery;
    }

    private function translateConditionList(ConditionList $list, SelectResult $parentResult)
    {
        $new = new ConditionList();
        foreach($list as $cond) {
            $cnd = $cond->getCondition();
            if ($cnd instanceof ConditionList) {
                $cond->setCondition($this->translateConditionList($cnd, $parentResult));
            } elseif ($cnd instanceof NestOperationResolverInterface && $cnd->requiresTransform()) {
                $cnd = $cnd->nestTransform($parentResult);
                $cond->setCondition($cnd);
            }
            $new->add($cond);
        }
        return $new;
    }

    public static function valueProxyToArray(ValueProxy $proxy, SelectResult $parentResult) : SqlValueArray
    {
        return new SqlValueArray($parentResult->getColumnValues($proxy->getName()));
    }

    public static function valueProxyToMin(ValueProxy $proxy, SelectResult $parentResult) : SqlValue
    {
        return new SqlValue(min($parentResult->getColumnValues($proxy->getName())));
    }

    public static function valueProxyToMax(ValueProxy $proxy, SelectResult $parentResult) : SqlValue
    {
        return new SqlValue(max($parentResult->getColumnValues($proxy->getName())));
    }

    public static function valueProxyToMergedArray(ValueProxy $proxy, SelectResult $parentResult) : SqlValueArray
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