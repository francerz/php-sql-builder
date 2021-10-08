<?php

use Francerz\SqlBuilder\Components\Table;
use Francerz\SqlBuilder\SelectQuery;
use PHPUnit\Framework\TestCase;

class TableTest extends TestCase
{
    public function testCreateFromString()
    {
        $table = Table::fromExpression('table1');
        $this->assertEquals('table1', $table->getSource());
        $this->assertNull($table->getAlias());

        $table = Table::fromExpression('table1 AS t1');
        $this->assertEquals('table1', $table->getSource());
        $this->assertEquals('t1', $table->getAlias());
    }

    public function testCreateFromQuery()
    {
        $query = new SelectQuery();
        $table = Table::fromExpression('t1', $query);
        $this->assertEquals($query, $table->getSource());
        $this->assertEquals('t1', $table->getAlias());
    }

    public function testCreateFromCallable()
    {
        $table = Table::fromExpression('t1', function ($q) {
        });
        $this->assertInstanceOf(SelectQuery::class, $table->getSource());
        $this->assertEquals('t1', $table->getAlias());
    }

    public function testCreateFromArray()
    {
        $table = Table::fromExpression(['table1']);
        $this->assertEquals('table1', $table->getSource());
        $this->assertNull($table->getAlias());

        $table = Table::fromExpression(['t1' => 'table1']);
        $this->assertEquals('table1', $table->getSource());
        $this->assertEquals('t1', $table->getAlias());

        $query = new SelectQuery();
        $table = Table::fromExpression(['t1' => $query]);
        $this->assertEquals($query, $table->getSource());
        $this->assertEquals('t1', $table->getAlias());

        $table = Table::fromExpression(['t1' => function ($q) {
        }]);
        $this->assertInstanceOf(SelectQuery::class, $table->getSource());
        $this->assertEquals('t1', $table->getAlias());
    }
}
