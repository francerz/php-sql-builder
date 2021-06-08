<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\Driver\DriverInterface;
use InvalidArgumentException;
use LogicException;

class DriverManager
{
    private static $drivers = [];

    public static function register(string $name, DriverInterface $driver)
    {
        if (array_key_exists($name, static::$drivers)) {
            throw new InvalidArgumentException("Another driver with same name already bounded");
        }
        static::$drivers[$name] = $driver;
    }

    public static function getDriver(string $name) : ?DriverInterface
    {
        if (array_key_exists($name, static::$drivers)) {
            return static::$drivers[$name];
        }
        return null;
    }

    public static function fromEnv(string $alias, ?array $env = null)
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

        return $driver;
    }
}