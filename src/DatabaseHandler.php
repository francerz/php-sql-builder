<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\Drivers\DriverInterface;

class DatabaseHandler
{
    private $compiler;
    private $driver;

    public function setCompiler(CompilerInterface $compiler)
    {
        $this->compiler = $compiler;
    }

    public function setDriver(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    public function execute(QueryInterface $query)
    {
        $compiled = $this->compiler->compile($query);
        $result = $this->driver->execute($compiled);

        return $result;
    }
}