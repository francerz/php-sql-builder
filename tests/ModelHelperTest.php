<?php

namespace Francerz\SqlBuilder\Tests;

use Francerz\SqlBuilder\Dev\Model\User;
use Francerz\SqlBuilder\Helpers\ModelHelper;
use PHPUnit\Framework\TestCase;

class ModelHelperTest extends TestCase
{
    public function testDataAsArray()
    {
        $user = new User();
        $user->user_id = 20;
        $user->username = 'myname';
        $user->enabled = true;
        $user->loggedIn = false;

        $actual = ModelHelper::dataAsArray($user, true);

        $expected = [
            'user_id' => 20,
            'username' => 'myname',
            'enabled'   => true
        ];

        $this->assertEquals($expected, $actual);
    }
}
