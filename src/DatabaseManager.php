<?php

namespace Francerz\SqlBuilder;

use LogicException;
use Psr\Http\Message\UriInterface;

class DatabaseManager
{
    /** @var ConnectParams[] */
    private static $params = [];

    /** @var DatabaseHandler[] */
    private static $connections = [];
    /**
     * Connects to a database
     *
     * @param string|ConnectParams $database
     * @param array|null $env
     * @return DatabaseHandler
     */
    public static function connect($database = 'default', bool $recycle = true): DatabaseHandler
    {
        if (is_string($database)) {
            if (array_key_exists($database, static::$params)) {
                $connParams = static::$params[$database];
            } else {
                $connParams = ConnectParams::fromEnv($database);
            }
        } elseif ($database instanceof UriInterface) {
            $connParams = ConnectParams::fromUri($database);
        } elseif ($database instanceof ConnectParams) {
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

    public static function register(string $alias, ConnectParams $params, bool $overwrite = false)
    {
        if (!$overwrite && array_key_exists($alias, static::$params)) {
            throw new LogicException("Database alias '{$alias}' already used.");
        }
        static::$params[$alias] = $params;
    }

    public static function find(string $alias)
    {
        if (array_key_exists($alias, static::$params)) {
            return static::$params[$alias];
        }
        return null;
    }
}
