<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\Driver\DriverInterface;
use InvalidArgumentException;

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
}