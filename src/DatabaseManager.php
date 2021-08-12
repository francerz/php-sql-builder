<?php

namespace Francerz\SqlBuilder;

use Francerz\Http\Uri;
use LogicException;
class DatabaseManager
{
    private static $connections = [];
    /**
     * Connects to a database
     *
     * @param string|ConnectParams $database
     * @param array|null $env
     * @return DatabaseHandler
     */
    public static function connect($database = 'default', bool $recycle = true) : DatabaseHandler
    {
        if (is_string($database)) {
            if (filter_var($database, FILTER_VALIDATE_URL)) {
                $connParams = ConnectParams::fromUri(new Uri($database));
            } else {
                $connParams = ConnectParams::fromEnv($database);
            }
        }

        if ($database instanceof ConnectParams) {
            $connParams = $database;
        }

        $dbKey = (string)$connParams;
        if ($recycle && isset(static::$connections[$dbKey])) {
            return static::$connections[$dbKey];
        }

        $db = new DatabaseHandler($connParams->getDriver());
        $db->connect($connParams);
        return static::$connections[$dbKey] = $db;
    }
}