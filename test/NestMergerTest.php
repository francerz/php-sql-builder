<?php

use Francerz\SqlBuilder\CompiledQuery;
use Francerz\SqlBuilder\Nesting\NestedSelect;
use Francerz\SqlBuilder\Nesting\NestMerger;
use Francerz\SqlBuilder\Nesting\RowProxy;
use Francerz\SqlBuilder\Query;
use Francerz\SqlBuilder\Results\SelectResult;
use PHPUnit\Framework\TestCase;

class NestMergerTest extends TestCase
{
    public function testMerging()
    {
        $query = Query::selectFrom('table1');
        $nested = Query::selectFrom('table2');
        $nested->where()->lessEquals('childId', 4);

        $query->nest(['Nest'=>$nested], function(NestedSelect $nest, RowProxy $row) {
            $nest->getSelect()->where('childCol', $row->parentId);
        });

        $parents = new SelectResult(new CompiledQuery('',[], $query), json_decode(json_encode(array(
            ['parentId'=>1],
            ['parentId'=>2],
            ['parentId'=>3],
            ['parentId'=>1]
        ))));

        $children = new SelectResult(new CompiledQuery('',[], $query), json_decode(json_encode(array(
            ['childId'=>1, 'childCol'=>2],
            ['childId'=>2, 'childCol'=>1],
            ['childId'=>3, 'childCol'=>1],
            ['childId'=>4, 'childCol'=>3]
        ))));

        $merger = new NestMerger();
        $merger->merge($parents, $children, $query->getNests()[0]);

        $this->assertCount(2, $parents[0]->Nest);
        $this->assertCount(1, $parents[1]->Nest);
        $this->assertCount(1, $parents[2]->Nest);
        $this->assertCount(2, $parents[3]->Nest);
    }
}