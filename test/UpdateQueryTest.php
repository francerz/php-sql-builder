<?php

use Francerz\SqlBuilder\GenericCompiler;
use Francerz\SqlBuilder\Query;
use PHPUnit\Framework\TestCase;

class UpdateQueryTest extends TestCase
{
    private $compiler;

    public function __construct()
    {
        parent::__construct();
        $this->compiler = new GenericCompiler();    
    }

    public function testUpdateSimple()
    {
        $obj = new stdClass();
        $obj->attr1 = 'alpha';
        $obj->attr2 = 'bravo';
        $obj->pk_id = 123;

        $query = Query::update(['t1'=>'table'], $obj, ['pk_id']);

        $compiled = $this->compiler->compile($query);

        $this->assertEquals(
            "UPDATE table AS t1 SET t1.attr1 = :v1, t1.attr2 = :v2 WHERE t1.pk_id = :v3",
            $compiled->getQuery()
        );
    }
}