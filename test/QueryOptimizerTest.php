<?php

use Francerz\SqlBuilder\Query;
use Francerz\SqlBuilder\SelectQuery;
use Francerz\SqlBuilder\Tools\QueryOptimizer;
use PHPUnit\Framework\TestCase;

class QueryOptimizerTest extends TestCase
{
    public function testSubqueryFilterSingleOperand()
    {
        $query = Query::selectFrom(['a'=>'table_a']);
        $query->innerJoin(['b'=>'table_b'])->on('b.id', 'a.id');
        $query->where()->null('b.key')->null('b.key2');

        $expected = Query::selectFrom(['a'=>'table_a']);
        $expected->innerJoin(['b'=>function(SelectQuery $subquery) {
            $subquery->from('table_b');
            $subquery->where()->null('key')->null('key2');
        }])->on('b.id','a.id');
        $expected->where()->null('b.key')->null('b.key2');

        $optimized = QueryOptimizer::optimizeSelect($query);
        $this->assertEquals($expected, $optimized);
    }

    public function testSubqueryFilterTwoOperands()
    {
        $query = Query::selectFrom(['a'=>'table_a']);
        $query->innerJoin(['b'=>'table_b'])->on('b.id', 'a.id');
        $query->where('b.key', 8)->andGreaterEquals('b.key', 7);

        $expected = Query::selectFrom(['a'=>'table_a']);
        $expected->innerJoin(['b'=>function(SelectQuery $subquery) {
            $subquery->from('table_b');
            $subquery->where('key', 8);
            $subquery->where()->greaterEquals('key', 7);
        }])->on('b.id','a.id');
        $expected->where('b.key', 8)->andGreaterEquals('b.key', 7);


        $optimized = QueryOptimizer::optimizeSelect($query);
        $this->assertEquals($expected, $optimized);
    }
}