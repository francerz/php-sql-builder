<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\Components\Nest;
use Francerz\SqlBuilder\Driver\DriverInterface;
use Francerz\SqlBuilder\Driver\QueryCompiler;
use Francerz\SqlBuilder\Driver\QueryCompilerInterface;
use Francerz\SqlBuilder\Driver\QueryTranslatorInterface;
use Francerz\SqlBuilder\Nesting\NestMerger;
use Francerz\SqlBuilder\Nesting\NestTranslator;
use Francerz\SqlBuilder\Results\QueryResultInterface;

class DatabaseHandler
{
    private $compiler;
    private $translator;
    private $driver;

    private $nestTranslator;
    private $nestMerger;

    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
        $this->compiler = $driver->getCompiler() ?? new QueryCompiler();
        $this->translator = $driver->getTranslator();
        $this->nestTranslator = new NestTranslator();
        $this->nestMerger = new NestMerger();
    }

    public function setDriver(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    public function setCompiler(QueryCompilerInterface $compiler)
    {
        $this->compiler = $compiler;
    }

    public function setTranslator(QueryTranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function connect(ConnectParams $params)
    {
        $this->driver->connect($params);
    }

    public function execute(QueryInterface $query) : QueryResultInterface
    {
        if (isset($this->translator)) {
            $query = $this->translator->translateQuery($query);
        }
        $compiled = $this->compiler->compileQuery($query);
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