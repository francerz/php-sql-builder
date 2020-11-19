<?php

namespace Francerz\SqlBuilder;

class ConnectParams
{
    private $host;
    private $user;
    private $password;
    private $port;
    private $database;

    public function __construct(string $host, string $user, string $password, string $database, int $port)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->port = $port;
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
    public function getUser() : string
    {
        return $this->user;
    }
    public function setPassword(string $password)
    {
        $this->password = $password;
    }
    public function getPassword() : string
    {
        return $this->password;
    }
    public function setDatabase(string $database)
    {
        $this->database = $database;
    }
    public function getDatabase()
    {
        return $this->database;
    }

    public function setPort(int $port)
    {
        $this->port = $port;
    }
    public function getPort()
    {
        return $this->port;
    }
}