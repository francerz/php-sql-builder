<?php

namespace Francerz\SqlBuilder;

use LogicException;
use SebastianBergmann\Type\StaticType;

class DatabaseManager
{
    public static function connect(string $alias = 'default', ?array $env = null) : DatabaseHandler
    {
        $env = is_null($env) ? $_ENV : $env;

        $driverKey = "database.{$alias}.driver";
        if (!array_key_exists($driverKey, $env)) {
            throw new LogicException("Missing {$driverKey} setting in `.env` file.");
        }

        $driver = DriverManager::getDriver($env[$driverKey]);
        if (is_null($driver)) {
            throw new LogicException("Unknown '{$env[$driverKey]}' driver.");
        }

        $hostKey = "database.{$alias}.host";
        $portKey = "database.{$alias}.port";
        $userKey = "database.{$alias}.user";
        $pswdKey = "database.{$alias}.pswd";
        $nameKey = "database.{$alias}.name";
        $encdKey = "database.{$alias}.encd";

        $host = array_key_exists($hostKey, $env) ? $env[$hostKey] : $driver->getDefaultHost();
        $port = array_key_exists($portKey, $env) ? $env[$portKey] : $driver->getDefaultPort();
        $user = array_key_exists($userKey, $env) ? $env[$userKey] : $driver->getDefaultUser();
        $pswd = array_key_exists($pswdKey, $env) ? $env[$pswdKey] : $driver->getDefaultPswd();
        $name = array_key_exists($nameKey, $env) ? $env[$nameKey] : $alias;
        $encd = array_key_exists($encdKey, $env) ? $env[$encdKey] : null;

        $connParams = new ConnectParams($host, $user, $pswd, $name, $port, $encd);

        $db = new DatabaseHandler($driver);
        $db->connect($connParams);
        return $db;
    }
}