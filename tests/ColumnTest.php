<?php

namespace Francerz\SqlBuilder\Tests;

use Francerz\SqlBuilder\Components\Column;
use Francerz\SqlBuilder\Components\SqlRaw;
use PHPUnit\Framework\TestCase;

class ColumnTest extends TestCase
{
    public function testCreateColumn()
    {
        $col = new Column('column_name', 'cn', 't1');

        $this->assertEquals($col, Column::fromString('t1.column_name AS cn'));
        $this->assertEquals($col, Column::fromArray(['cn' => 't1.column_name'])[0]);
    }

    public function testFromExpression()
    {
        $expected = new Column('name', 'alias', 'table');
        $actual = Column::fromExpression('table.name AS alias');
        $this->assertEquals($expected, $actual);

        $expected = new Column(new SqlRaw('COUNT(*)'), 'total');
        $this->assertEquals($expected, Column::fromExpression('COUNT(*) AS total'));
        $this->assertEquals($expected, Column::fromExpression(['total' => 'COUNT(*)']));
    }
}
