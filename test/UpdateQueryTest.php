<?php

namespace Francerz\SqlBuilder\Tests;

use Francerz\SqlBuilder\Dev\Model\User;
use Francerz\SqlBuilder\Driver\QueryCompiler;
use Francerz\SqlBuilder\Query;
use Francerz\SqlBuilder\UpdateQuery;
use PHPUnit\Framework\TestCase;
use stdClass;

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

        $compiled = $this->compiler->compileUpdate($query);

        $this->assertEquals(
            "UPDATE table AS t1 SET t1.attr1 = :v1, t1.attr2 = :v2, t1.pk_id = :v3 WHERE t1.pk_id = :v4",
            $compiled->getQuery()
        );

        $query = Query::update(['t1' => 'table'], $obj, ['pk_id'], ['attr2']);

        $compiled = $this->compiler->compileUpdate($query);

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

    public function testMatchingValues()
    {
        $group = json_decode(json_encode([
            'group_id' => 3,
            'year' => 2023,
            'topic' => 'Data Science'
        ]));
        $query = Query::update('groups', $group, ['group_id' => 5, 'year'], ['topic']);

        $compiled = $this->compiler->compileUpdate($query);

        $this->assertEquals(
            'UPDATE groups SET groups.topic = :v1 WHERE groups.group_id = :v2 AND groups.year = :v3',
            $compiled->getQuery()
        );
        $this->assertEquals(
            ['v1' => 'Data Science', 'v2' => 5, 'v3' => 2023],
            $compiled->getValues()
        );
    }
}
