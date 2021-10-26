<?php

namespace Francerz\SqlBuilder\Tests;

use Francerz\SqlBuilder\CompiledQuery;
use Francerz\SqlBuilder\Nesting\NestTranslator;
use Francerz\SqlBuilder\Nesting\RowProxy;
use Francerz\SqlBuilder\Nesting\ValueProxy;
use Francerz\SqlBuilder\Query;
use Francerz\SqlBuilder\Results\SelectResult;
use PHPUnit\Framework\TestCase;

class NestTranslatorTest extends TestCase
{
    public function testTranslate()
    {
        $translator = new NestTranslator();

        $query = Query::selectFrom('table1');
        $query->where('col1', 10);
        $query->where('col2', new ValueProxy(new RowProxy(), 'test'));

        $result = new SelectResult(new CompiledQuery(''), [
            (object)['test' => 1],
            (object)['test' => 2],
            (object)['test' => 1]
        ]);

        $translated = $translator->translate($query, $result);

        $expected = Query::selectFrom('table1');
        $expected->where('col1', 10)->andIn('col2', [1, 2]);

        $this->assertEquals($expected, $translated);
    }
}
