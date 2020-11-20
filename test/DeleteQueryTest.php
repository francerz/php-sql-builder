<?php

use Francerz\SqlBuilder\GenericCompiler;
use Francerz\SqlBuilder\Query;
use PHPUnit\Framework\TestCase;

class DeleteQueryTest extends TestCase
{
    private $compiler;

    public function __construct()
    {
        parent::__construct();
        $this->compiler = new GenericCompiler();
    }

    public function testDeleteCase()
    {
        $query = Query::deleteFrom('table1', ['key'=>15]);
        $compiled = $this->compiler->compile($query);

        $this->assertEquals('DELETE FROM table1 WHERE key = :v1', $compiled->getQuery());
        $this->assertEquals(['v1'=>15], $compiled->getValues());
    }
}