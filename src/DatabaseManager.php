<?php

namespace Francerz\SqlBuilder;

use LogicException;
class DatabaseManager
{
    public static function connect(string $alias = 'default', ?array $env = null) : DatabaseHandler
    {
        $env = is_null($env) ? $_ENV : $env;

        $alias = strtoupper($alias);

        $driverKey = "DATABASE_{$alias}_DRIVER";
        if (!array_key_exists($driverKey, $env)) {
            throw new LogicException("Missing {$driverKey} setting in `.env` file.");
        }

        $driver = DriverManager::getDriver($env[$driverKey]);
        if (is_null($driver)) {
            throw new LogicException("Unknown '{$env[$driverKey]}' driver.");
        }

        $hostKey = "DATABASE_{$alias}_HOST";
        $portKey = "DATABASE_{$alias}_PORT";
        $userKey = "DATABASE_{$alias}_USER";
        $pswdKey = "DATABASE_{$alias}_PSWD";
        $nameKey = "DATABASE_{$alias}_NAME";
        $encdKey = "DATABASE_{$alias}_ENCD";

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