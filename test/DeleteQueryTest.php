<?php

use Francerz\SqlBuilder\Driver\QueryCompiler;
use Francerz\SqlBuilder\Query;
use PHPUnit\Framework\TestCase;

class DeleteQueryTest extends TestCase
{
    private $compiler;

    public function __construct()
    {
        parent::__construct();
        $this->compiler = new QueryCompiler();
    }

    public function testDeleteCase()
    {
        $query = Query::deleteFrom('table1', ['key' => 15]);
        $compiled = $this->compiler->compileQuery($query);

        $this->assertEquals('DELETE FROM table1 WHERE key = :v1', $compiled->getQuery());
        $this->assertEquals(['v1' => 15], $compiled->getValues());
    }
}
