<?php

namespace Francerz\SqlBuilder\Tests;

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

    public function testDeleteRowsIn()
    {
        $query = Query::deleteFrom(['t1' => 'table1']);
        $query->innerJoin(['t2' => 'table2'])
            ->on('t2.key1', 't1.key1');
        $query->where('t2.filter', 3);
        $query->rowsIn('t1');

        $compiled = $this->compiler->compileQuery($query);

        $expected = 'DELETE t1 FROM table1 AS t1 INNER JOIN table2 AS t2 ON t2.key1 = t1.key1 WHERE t2.filter = :v1';
        $this->assertEquals($expected, $compiled->getQuery());
        $this->assertEquals(['v1' => 3], $compiled->getValues());
    }
}
