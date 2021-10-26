<?php

use Francerz\SqlBuilder\ConnectParams;
use Francerz\SqlBuilder\DatabaseHandler;
use Francerz\SqlBuilder\DatabaseManager;
use Francerz\SqlBuilder\Dev\Driver\TestDriver;
use Francerz\SqlBuilder\DriverManager;
use PHPUnit\Framework\TestCase;

class DatabaseManagerTest extends TestCase
{
    public function testRegisterFindConnectParams()
    {
        DriverManager::register('test', new TestDriver());
        $params = ConnectParams::fromUri('test://user:pswd@host:1234/database');

        DatabaseManager::register('test', $params);
        $this->assertEquals($params, DatabaseManager::find('test'));

        $db = DatabaseManager::connect('test');
        $this->assertNotNull($db);
        $this->assertInstanceOf(DatabaseHandler::class, $db);
    }
}
