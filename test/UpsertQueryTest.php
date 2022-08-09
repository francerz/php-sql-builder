<?php

use Francerz\SqlBuilder\Query;
use PHPUnit\Framework\TestCase;

class UpsertQueryTest extends TestCase
{
    public function testUpsertColumns()
    {
        $obj = new stdClass();
        $obj->user_id = 13;
        $obj->username = 'myuser';
        $obj->password = 'P455sw0Rd#ash';

        $query = Query::upsert('users', $obj, ['user_id']);
        $this->assertEquals(['user_id', 'username', 'password'], $query->getColumns());
        $this->assertEquals(['user_id'], $query->getKeys());
        $this->assertEquals(['username', 'password'], $query->getUpdateColumns());

        $query = Query::upsert('users', $obj, ['user_id'], ['username', 'password']);
        $this->assertEquals(['user_id', 'username', 'password'], $query->getColumns());
        $this->assertEquals(['user_id'], $query->getKeys());
        $this->assertEquals(['username', 'password'], $query->getUpdateColumns());

        $query = Query::upsert('users', $obj, ['user_id'], ['username', 'password', 'enabled']);
        $this->assertEquals(['user_id', 'username', 'password', 'enabled'], $query->getColumns());
        $this->assertEquals(['user_id'], $query->getKeys());
        $this->assertEquals(['username', 'password', 'enabled'], $query->getUpdateColumns());
    }
}
