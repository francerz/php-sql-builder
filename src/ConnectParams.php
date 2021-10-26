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

    public function __construct(
        DriverInterface $driver,
        string $host,
        ?string $user = null,
        ?string $password = null,
        ?string $database = null,
        ?int $port = null,
        ?string $encoding = null
    ) {
        $this->driver = $driver;
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->port = $port;
        $this->encoding = $encoding;
    }

    public static function fromEnv(string $alias, ?array $env = null)
    {
        $env = is_null($env) ? $_ENV : $env;
        $alias = strtoupper($alias);

        $driver = DriverManager::fromEnv($alias, $env);
        $hostKey = "DATABASE_{$alias}_HOST";
        $portKey = "DATABASE_{$alias}_PORT";
        $userKey = "DATABASE_{$alias}_USER";
        $pswdKey = "DATABASE_{$alias}_PSWD";
        $nameKey = "DATABASE_{$alias}_NAME";
        $encdKey = "DATABASE_{$alias}_ENCD";

        $host = $env[$hostKey] ?? $driver->getDefaultHost();
        $port = $env[$portKey] ?? $driver->getDefaultPort();
        $user = $env[$userKey] ?? $driver->getDefaultUser();
        $pswd = $env[$pswdKey] ?? $driver->getDefaultPswd();
        $name = $env[$nameKey] ?? $alias;
        $encd = $env[$encdKey] ?? null;

        return new ConnectParams($driver, $host, $user, $pswd, $name, $port, $encd);
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
}
