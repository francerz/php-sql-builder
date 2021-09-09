<?php

use Francerz\SqlBuilder\Query;
use Francerz\SqlBuilder\SelectQuery;
use Francerz\SqlBuilder\Tools\QueryOptimizer;
use PHPUnit\Framework\TestCase;

class QueryOptimizerTest extends TestCase
{
    public function testSubuqeryFilterSingleOperandNotJoin()
    {
        $query = Query::selectFrom(['a'=>'table_a']);
        $query->where()->null('a.key');

        $expected = Query::selectFrom(['a'=>'table_a']);
        $expected->where()->null('a.key');

        $optimized = QueryOptimizer::optimizeSelect($query);
        $this->assertEquals($expected, $optimized);
    }
    public function testSubqueryFilterSingleOperand()
    {
        $query = Query::selectFrom(['a'=>'table_a']);
        $query->innerJoin(['b'=>function(SelectQuery $subquery) {
            $subquery->from(['b'=>'table_b']);
            $subquery->innerJoin(['c'=>'table_c'])->on('b.fk','c.pk');
        }])->on('b.id', 'a.id');
        $query->where()->null('b.key');

        $expected = Query::selectFrom(['a'=>'table_a']);
        $expected->innerJoin(['b'=>function(SelectQuery $subquery) {
            $subquery->from(['b'=>'table_b']);
            $subquery->innerJoin(['c'=>'table_c'])->on('b.fk','c.pk');
            $subquery->where()->null('key');
        }])->on('b.id', 'a.id');
        $expected->where()->null('b.key');

        $optimized = QueryOptimizer::optimizeSelect($query);
        $this->assertEquals($expected, $optimized);
    }

    public function testSubqueryFilterTwoOperands()
    {
        $query = Query::selectFrom(['a'=>'table_a']);
        $query->innerJoin(['b'=>function(SelectQuery $subquery) {
            $subquery->from('table_b');
            $subquery->innerJoin(['c'=>'table_c'])->on('b.fk','c.pk');
        }])->on('b.id', 'a.id');
        $query->where('b.key', 8)->andGreaterEquals('b.key', 7);

        $expected = Query::selectFrom(['a'=>'table_a']);
        $expected->innerJoin(['b'=>function(SelectQuery $subquery) {
            $subquery->from('table_b');
            $subquery->innerJoin(['c'=>'table_c'])->on('b.fk','c.pk');
            $subquery->where('key', 8)
                ->greaterEquals('key', 7);
        }])->on('b.id','a.id');
        $expected->where('b.key', 8)->andGreaterEquals('b.key', 7);


        $optimized = QueryOptimizer::optimizeSelect($query);
        $this->assertEquals($expected, $optimized);
    }
}