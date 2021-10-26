<?php

use Francerz\SqlBuilder\Dev\Model\User;
use Francerz\SqlBuilder\Driver\QueryCompiler;
use Francerz\SqlBuilder\Query;
use Francerz\SqlBuilder\UpdateQuery;
use PHPUnit\Framework\TestCase;

class UpdateQueryTest extends TestCase
{
    private $compiler;

    public function __construct()
    {
        parent::__construct();
        $this->compiler = new QueryCompiler();
    }

    public function testUpdateSimple()
    {
        $obj = new stdClass();
        $obj->attr1 = 'alpha';
        $obj->attr2 = 'bravo';
        $obj->pk_id = 123;

        $query = Query::update(['t1' => 'table'], $obj, ['pk_id']);

        $compiled = $this->compiler->compileQuery($query);

        $this->assertEquals(
            "UPDATE table AS t1 SET t1.attr1 = :v1, t1.attr2 = :v2, t1.pk_id = :v3 WHERE t1.pk_id = :v4",
            $compiled->getQuery()
        );

        $query = Query::update(['t1' => 'table'], $obj, ['pk_id'], ['attr2']);

        $compiled = $this->compiler->compileQuery($query);

        $this->assertEquals(
            "UPDATE table AS t1 SET t1.attr2 = :v1 WHERE t1.pk_id = :v2",
            $compiled->getQuery()
        );
    }

    public function testCreateUpdateWithClass()
    {
        $user = new User();
        $user->user_id = 5;
        $user->username = 'user';
        $user->enabled = 1;
        $user->loggedIn = 0;

        $actual = Query::update('users', $user);

        $expected = new UpdateQuery('users');
        $expected->set('username', 'user');
        $expected->set('enabled', 1);
        $expected->where('users.user_id', 5);

        $this->assertTrue(true);
        $this->assertEquals($expected, $actual);
    }
}
