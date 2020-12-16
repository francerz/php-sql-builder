<?php

namespace Francerz\SqlBuilder;

class ConnectParams
{
    private $host;
    private $user;
    private $password;
    private $port;
    private $database;
    private $encoding;

    public function __construct(
        string $host,
        ?string $user = null,
        ?string $password = null,
        ?string $database = null,
        ?int $port = null,
        ?string $encoding = null
    ) {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->port = $port;
        $this->encoding = $encoding;
    }

    public function setHost(string $host)
    {
        $this->host = $host;
    }
    public function getHost() : string
    {
        return $this->host;
    }
    public function setUser(string $user)
    {
        $this->user = $user;
    }
    public function getUser() : ?string
    {
        return $this->user;
    }
    public function setPassword(string $password)
    {
        $this->password = $password;
    }
    public function getPassword() : ?string
    {
        return $this->password;
    }
    public function setDatabase(string $database)
    {
        $this->database = $database;
    }
    public function getDatabase() : ?string
    {
        return $this->database;
    }

    public function setPort(int $port)
    {
        $this->port = $port;
    }
    public function getPort() : ?int
    {
        return $this->port;
    }

    public function setEncoding(string $encoding)
    {
        $this->encoding = $encoding;
    }
    public function getEncoding() : ?string
    {
        return $this->encoding;
    }
}