<?php

namespace Francerz\SqlBuilder;

use Francerz\Http\Uri;
use LogicException;
class DatabaseManager
{
    /**
     * Connects to a database
     *
     * @param string|ConnectParams $database
     * @param array|null $env
     * @return DatabaseHandler
     */
    public static function connect($database = 'default', ?array $env = null) : DatabaseHandler
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

        $db = new DatabaseHandler($connParams->getDriver());
        $db->connect($connParams);
        return $db;
    }

    private static function getParams($database, ?array $env = null) : ConnectParams
    {
        if ($database instanceof ConnectParams) {
            return $database;
        }

        if (is_string($database)) {
            return null;
        }

        if (is_string($database) && filter_var($database, FILTER_VALIDATE_URL)) {
            return ConnectParams::fromUri(new Uri($database));
        }
    }
}