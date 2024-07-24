<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\Driver\DriverInterface;
use InvalidArgumentException;
use LogicException;
use Psr\Http\Message\UriInterface;

class ConnectParams
{
    private $driver;
    private $host;
    private $user;
    private $password;
    private $port;
    private $database;
    private $encoding;
    private $instance;

    public function __construct(
        DriverInterface $driver,
        string $host,
        ?string $user = null,
        ?string $password = null,
        ?string $database = null,
        ?int $port = null,
        ?string $encoding = null,
        ?string $instance = null
    ) {
        $this->driver = $driver;
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->port = $port;
        $this->encoding = $encoding;
        $this->instance = $instance;
    }

    public static function fromEnv(string $alias)
    {
        $alias = strtoupper($alias);
        
        $driverKey = "DATABASE_{$alias}_DRIVER";
        $driverVal = static::getenv($driverKey);
        if (empty($driverVal)) {
            throw new LogicException("Missing environment variable {$driverKey}.");
        }
        $driver = DriverManager::getDriver($driverVal);
        if (empty($driver)) {
            throw new LogicException("Unknown driver {$driverVal}.");
        }

        $hostKey = "DATABASE_{$alias}_HOST";
        $portKey = "DATABASE_{$alias}_PORT";
        $userKey = "DATABASE_{$alias}_USER";
        $nameKey = "DATABASE_{$alias}_NAME";
        $encdKey = "DATABASE_{$alias}_ENCD";
        $pswdKey = "DATABASE_{$alias}_PSWD";
        $pwflKey = "DATABASE_{$alias}_PSWD_FILE";
        $instKey = "DATABASE_{$alias}_INST";

        $host = static::getenv($hostKey) ?? $driver->getDefaultHost();
        $port = static::getenv($portKey) ?? $driver->getDefaultPort();
        $user = static::getenv($userKey) ?? $driver->getDefaultUser();
        $name = static::getenv($nameKey) ?? $alias;
        $encd = static::getenv($encdKey) ?? null;
        $inst = static::getenv($instKey) ?? null;

        $pswd = static::getenv($pswdKey) ?? $driver->getDefaultPswd();
        if (!empty(static::getenv($pwflKey)) && file_exists(static::getenv($pwflKey))) {
            $pswd = file_get_contents(static::getenv($pwflKey));
        }

        return new ConnectParams($driver, $host, $user, $pswd, $name, $port, $encd, $inst);
    }

    private static function getenv(string $varname, bool $localOnly = false)
    {
        return getenv($varname, $localOnly) ?: $_ENV[$varname] ?? null;
    }

    /**
     * @param UriInterface|string $uri
     * @return ConnectParams
     */
    public static function fromUri($uri)
    {
        if ($uri instanceof UriInterface) {
            $uri = (string)$uri;
        }

        if (filter_var($uri, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException("Invalid URL '{$uri}'.");
        }
        $uriParts = parse_url($uri);
        $driverKey = $uriParts['scheme'] ?: null;
        $driver = DriverManager::getDriver($driverKey);

        if (is_null($driver)) {
            throw new LogicException("Missing driver {$driverKey}.");
        }

        return new ConnectParams(
            $driver,
            $uriParts['host'] ?? '',
            $uriParts['user'] ?? '',
            $uriParts['pass'] ?? '',
            ltrim($uriParts['path'] ?? '', '/'),
            $uriParts['port'] ?? 0
        );
    }

    public function __toString()
    {
        $name = DriverManager::getDriverName($this->driver) ?? 'unknown';
        return "{$name}://{$this->user}:{$this->password}@{$this->host}:{$this->port}/{$this->database}";
    }

    public function setDriver(DriverInterface $driver)
    {
        $this->driver = $driver;
    }
    public function getDriver()
    {
        return $this->driver;
    }

    public function setHost(string $host)
    {
        $this->host = $host;
    }
    public function getHost(): string
    {
        return $this->host;
    }
    public function setUser(string $user)
    {
        $this->user = $user;
    }
    public function getUser(): ?string
    {
        return $this->user;
    }
    public function setPassword(string $password)
    {
        $this->password = $password;
    }
    public function getPassword(): ?string
    {
        return $this->password;
    }
    public function setDatabase(string $database)
    {
        $this->database = $database;
    }
    public function getDatabase(): ?string
    {
        return $this->database;
    }

    public function setPort(int $port)
    {
        $this->port = $port;
    }
    public function getPort(): ?int
    {
        return $this->port;
    }

    public function setEncoding(string $encoding)
    {
        $this->encoding = $encoding;
    }
    public function getEncoding(): ?string
    {
        return $this->encoding;
    }

    public function setInstance(?string $instance)
    {
        $this->instance = $instance;
    }

    public function getInstance(): ?string
    {
        return $this->instance;
    }
}
