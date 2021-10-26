<?php

namespace Francerz\SqlBuilder\Tests;

use Francerz\SqlBuilder\Query;
use Francerz\SqlBuilder\SelectQuery;
use Francerz\SqlBuilder\Tools\QueryOptimizer;
use PHPUnit\Framework\TestCase;

class QueryOptimizerTest extends TestCase
{
    public function testSubuqeryFilterSingleOperandNotJoin()
    {
        $query = Query::selectFrom(['a' => 'table_a']);
        $query->where()->null('a.key');

        $expected = Query::selectFrom(['a' => 'table_a']);
        $expected->where()->null('a.key');

        $optimized = QueryOptimizer::optimizeSelect($query);
        $this->assertEquals($expected, $optimized);
    }
    public function testSubqueryFilterSingleOperand()
    {
        $query = Query::selectFrom(['a' => 'table_a']);
        $query->innerJoin(['b' => function (SelectQuery $subquery) {
            $subquery->from(['b' => 'table_b']);
            $subquery->innerJoin(['c' => 'table_c'])->on('b.fk', 'c.pk');
        }])->on('b.id', 'a.id');
        $query->where()->null('b.key');

        $expected = Query::selectFrom(['a' => 'table_a']);
        $expected->innerJoin(['b' => function (SelectQuery $subquery) {
            $subquery->from(['b' => 'table_b']);
            $subquery->innerJoin(['c' => 'table_c'])->on('b.fk', 'c.pk');
            $subquery->where()->null('key');
        }])->on('b.id', 'a.id');
        $expected->where()->null('b.key');

        $optimized = QueryOptimizer::optimizeSelect($query);
        $this->assertEquals($expected, $optimized);
    }

    public function testSubqueryFilterTwoOperands()
    {
        $query = Query::selectFrom(['a' => 'table_a']);
        $query->innerJoin(['b' => function (SelectQuery $subquery) {
            $subquery->from('table_b');
            $subquery->innerJoin(['c' => 'table_c'])->on('b.fk', 'c.pk');
        }])->on('b.id', 'a.id');
        $query->where('b.key', 8)->andGreaterEquals('b.key', 7);

        $expected = Query::selectFrom(['a' => 'table_a']);
        $expected->innerJoin(['b' => function (SelectQuery $subquery) {
            $subquery->from('table_b');
            $subquery->innerJoin(['c' => 'table_c'])->on('b.fk', 'c.pk');
            $subquery->where('key', 8)
                ->greaterEquals('key', 7);
        }])->on('b.id', 'a.id');
        $expected->where('b.key', 8)->andGreaterEquals('b.key', 7);


        $optimized = QueryOptimizer::optimizeSelect($query);
        $this->assertEquals($expected, $optimized);
    }

    public function testOptimizeSubqueryFrom()
    {
        $zQuery = Query::selectFrom(['z' => 'table_z']);
        $yQuery = Query::selectFrom(['z' => $zQuery], ['id_z']);
        $aQuery = Query::selectFrom(['a' => $yQuery]);
        $aQuery->where('a.id_z', 8);


        $zExpected = Query::selectFrom(['z' => 'table_z']);
        $zExpected->where('id_z', 8);
        $yExpected = Query::selectFrom(['z' => $zExpected], ['id_z']);
        $yExpected->where('z.id_z', 8);
        $aExpected = Query::selectFrom(['a' => $yExpected]);
        $aExpected->where('a.id_z', 8);

        $optimized = QueryOptimizer::optimizeSelect($aQuery);
        $this->assertEquals($aExpected, $optimized);
    }

    public function testOptimizeSubqueryJoin()
    {
        $zQuery = Query::selectFrom(['z' => 'table_z']);
        $zQuery->innerJoin(['z2' => 'table_z'], ['id_z'])->on('z2.id_z', 'z.id_z');
        $yQuery = Query::selectFrom(['y' => 'table_y'], ['id_y']);
        $yQuery->innerJoin(['z' => $zQuery], ['id_z'])->on('z.id_z', 'y.id_z');
        $aQuery = Query::selectFrom(['a' => 'table_a'], ['id_a']);
        $aQuery->innerJoin(['y' => $yQuery], ['id_y', 'id_z'])->on('y.id_y', 'a.id_y');
        $aQuery->where('y.id_z', 8);

        $zExpected = Query::selectFrom(['z' => 'table_z']);
        $zExpected->innerJoin(['z2' => 'table_z'], ['id_z'])->on('z2.id_z', 'z.id_z');
        $zExpected->where('z2.id_z', 8);
        $yExpected = Query::selectFrom(['y' => 'table_y'], ['id_y']);
        $yExpected->innerJoin(['z' => $zExpected], ['id_z'])->on('z.id_z', 'y.id_z');
        $yExpected->where('z.id_z', 8);
        $aExpected = Query::selectFrom(['a' => 'table_a'], ['id_a']);
        $aExpected->innerJoin(['y' => $yExpected], ['id_y', 'id_z'])->on('y.id_y', 'a.id_y');
        $aExpected->where('y.id_z', 8);

        $optimized = QueryOptimizer::optimizeSelect($aQuery);
        $this->assertEquals($aExpected, $optimized);
    }
}
