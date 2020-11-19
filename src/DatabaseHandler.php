<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\Compiler\GenericCompiler;
use Francerz\SqlBuilder\Components\Nest;
use Francerz\SqlBuilder\Nesting\NestMerger;
use Francerz\SqlBuilder\Nesting\NestTranslator;
use Francerz\SqlBuilder\Results\QueryResultInterface;

class DatabaseHandler
{
    private $compiler;
    private $driver;
    private $nestTranslator;
    private $nestMerger;

    public function __construct(DriverInterface $driver, ?CompilerInterface $compiler = null)
    {
        $this->driver = $driver;
        $this->compiler = isset($compiler) ? $compiler : new GenericCompiler();
        $this->nestTranslator = new NestTranslator();
        $this->nestMerger = new NestMerger();
    }

    public function setDriver(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    public function setCompiler(CompilerInterface $compiler)
    {
        $this->compiler = $compiler;
    }

    public function connect(ConnectParams $params)
    {
        $this->driver->connect($params);
    }

    public function execute(QueryInterface $query) : QueryResultInterface
    {
        $compiled = $this->compiler->compile($query);
        $result = $this->driver->execute($compiled);

        if ($query instanceof SelectQuery) {
            foreach ($query->getNests() as $nest) {
                if (!$nest instanceof Nest) return null;
                $nestSelect = $nest->getNested()->getSelect();
                $nestTranslation = $this->nestTranslator->translate($nestSelect, $result);
                $nestResult = $this->execute($nestTranslation);
                $this->nestMerger->merge($result, $nestResult, $nest);
            }
        }

        return $result;
    }
}