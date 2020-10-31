<?php

use Francerz\SqlBuilder\Components\Column;
use PHPUnit\Framework\TestCase;

class ColumnTest extends TestCase
{
    public function testCreateColumn()
    {
        $col = new Column('column_name','cn', 't1');

        $this->assertEquals($col, Column::fromString('t1.column_name AS cn'));
        $this->assertEquals($col, Column::fromArray(['cn'=>'t1.column_name'])[0]);
    }
}