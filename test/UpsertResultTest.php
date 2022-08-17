<?php

namespace Francerz\SqlBuilder\Tests;

use Francerz\SqlBuilder\Results\UpsertResult;
use PHPUnit\Framework\TestCase;

class UpsertResultTest extends TestCase
{
    public function testGetters()
    {
        $result = new UpsertResult(['a'], ['b'], 3, 5, 13, true);

        $this->assertEquals(['a'], $result->getInserts());
        $this->assertEquals(['b'], $result->getUpdates());
        $this->assertEquals(3, $result->getNumInserted());
        $this->assertEquals(5, $result->getNumUpdated());
        $this->assertEquals(8, $result->getNumRows());
        $this->assertEquals(13, $result->getInsertedId());
        $this->assertTrue($result->success());
    }
}
