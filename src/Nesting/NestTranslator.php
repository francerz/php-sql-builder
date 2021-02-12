<?php

namespace Francerz\SqlBuilder\Nesting;

use Francerz\SqlBuilder\Components\SqlValue;
use Francerz\SqlBuilder\Components\SqlValueArray;
use Francerz\SqlBuilder\Expressions\Comparison\BetweenExpression;
use Francerz\SqlBuilder\Expressions\Comparison\InExpression;
use Francerz\SqlBuilder\Expressions\Comparison\RelationalExpression;
use Francerz\SqlBuilder\Expressions\Comparison\RelationalOperators;
use Francerz\SqlBuilder\Expressions\Logical\ConditionList;
use Francerz\SqlBuilder\Expressions\Logical\LogicConnectors;
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
            $cond->setConnector(LogicConnectors::OR);
            if ($cnd instanceof ConditionList) {
                $this->translateConditionList($cnd, $parentResult);
                continue;
            }
            if ($cnd instanceof NestOperationResolverInterface) {
                $cnd = $cnd->nestTransform($parentResult);
                if (isset($cnd)) {
                    $cond->setCondition($cnd);
                    continue;
                }
            }
            unset($list[$k]);
        }
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