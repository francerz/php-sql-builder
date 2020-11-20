<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\Components\Nest;
use Francerz\SqlBuilder\Driver\DriverInterface;
use Francerz\SqlBuilder\Driver\QueryCompiler;
use Francerz\SqlBuilder\Driver\QueryCompilerInterface;
use Francerz\SqlBuilder\Driver\QueryTranslatorInterface;
use Francerz\SqlBuilder\Nesting\NestMerger;
use Francerz\SqlBuilder\Nesting\NestTranslator;
use Francerz\SqlBuilder\Results\DeleteResult;
use Francerz\SqlBuilder\Results\InsertResult;
use Francerz\SqlBuilder\Results\QueryResultInterface;
use Francerz\SqlBuilder\Results\SelectResult;
use Francerz\SqlBuilder\Results\UpdateResult;
use InvalidArgumentException;

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

    private function translateQuery(QueryInterface $query)
    {
        if (isset($this->translator)) {
            $query = $this->translator->translateQuery($query);
        }
        return $query;
    }

    private function prepareQuery(QueryInterface $query) : ?CompiledQuery
    {
        if (isset($this->translator)) {
            $query = $this->translator->translateQuery($query);
        }
        return $this->compiler->compileQuery($query);
    }

    public function execute(QueryInterface $query) : QueryResultInterface
    {
        if ($query instanceof SelectQuery) {
            return $this->executeSelect($query);
        } elseif ($query instanceof InsertQuery) {
            return $this->executeInsert($query);
        } elseif ($query instanceof UpdateQuery) {
            return $this->executeUpdate($query);
        } elseif ($query instanceof DeleteQuery) {
            return $this->executeDelete($query);
        }

        throw new InvalidArgumentException('Unknown $query type.');
    }

    public function executeSelect(SelectQuery $query) : SelectResult
    {
        $compiled = $this->prepareQuery($query);
        $result = $this->driver->executeSelect($compiled);

        foreach ($query->getNests() as $nest) {
            if (!$nest instanceof Nest) return null;
            $nestSelect = $nest->getNested()->getSelect();
            $nestTranslation = $this->nestTranslator->translate($nestSelect, $result);
            $nestResult = $this->execute($nestTranslation);
            $this->nestMerger->merge($result, $nestResult, $nest);
        }

        return $result;
    }

    public function executeInsert(InsertQuery $query) : InsertResult
    {
        $compiled = $this->prepareQuery($query);
        $result = $this->driver->executeInsert($compiled);
        return $result;
    }

    public function executeUpdate(UpdateQuery $query) : UpdateResult
    {
        $compiled = $this->prepareQuery($query);
        $result = $this->driver->executeUpdate($compiled);
        return $result;
    }

    public function executeDelete(DeleteQuery $query) : DeleteResult
    {
        $compiled = $this->prepareQuery($query);
        $result = $this->driver->executeDelete($compiled);
        return $result;
    }
}