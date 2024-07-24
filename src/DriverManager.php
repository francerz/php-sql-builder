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

    public static function getDriver(string $name): ?DriverInterface
    {
        if (array_key_exists($name, static::$drivers)) {
            return clone static::$drivers[$name];
        }
        return null;
    }

    /**
     * Returns the driver name from DriverInterface object.
     *
     * @param DriverInterface $driver Driver object
     * @return string|null Returns the name of registered driver. Returns
     * **NULL** if driver is not registered.
     */
    public static function getDriverName(DriverInterface $driver): ?string
    {
        $name = array_keys(static::$drivers, $driver, true);
        return empty($name) ? null : reset($name);
    }

    /**
     * @deprecated
     *
     * @param string $alias
     * @param array|null $env
     * @return DriverInterface
     */
    public static function fromEnv(string $alias, ?array $env = null)
    {
        $driverKey = "DATABASE_{$alias}_DRIVER";
        $env = $env ?? getenv();
        $alias = strtoupper($alias);

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
