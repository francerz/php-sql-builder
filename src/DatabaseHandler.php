<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\Components\Nest;
use Francerz\SqlBuilder\Driver\DriverInterface;
use Francerz\SqlBuilder\Driver\QueryCompiler;
use Francerz\SqlBuilder\Driver\QueryCompilerInterface;
use Francerz\SqlBuilder\Driver\QueryTranslatorInterface;
use Francerz\SqlBuilder\Exceptions\DuplicateEntryException;
use Francerz\SqlBuilder\Nesting\NestMerger;
use Francerz\SqlBuilder\Nesting\NestTranslator;
use Francerz\SqlBuilder\Results\DeleteResult;
use Francerz\SqlBuilder\Results\InsertResult;
use Francerz\SqlBuilder\Results\QueryResultInterface;
use Francerz\SqlBuilder\Results\SelectResult;
use Francerz\SqlBuilder\Results\UpdateResult;
use Francerz\SqlBuilder\Results\UpsertResult;
use Francerz\SqlBuilder\Tools\QueryOptimizer;
use InvalidArgumentException;
use LogicException;

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

    /**
     * @deprecated v0.2.64 Use executeSelect, executeInsert, executeUpdate or executeDelete instead.
     */
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
        // $query = git QueryOptimizer::optimizeSelect($query);
        $compiled = $this->prepareQuery($query);
        $result = $this->driver->executeSelect($compiled);

        if (count($result) === 0) {
            return $result;
        }

        foreach ($query->getNests() as $nest) {
            if (!$nest instanceof Nest) return null;
            $nestSelect = $nest->getNested()->getSelect();
            $nestTranslation = $this->nestTranslator->translate($nestSelect, $result);
            $nestResult = $this->executeSelect($nestTranslation);
            $this->nestMerger->merge($result, $nestResult, $nest);
        }

        foreach ($query->getAfterExecuteActions() as $action) {
            $action($result);
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

    public function executeUpsert(UpsertQuery $query) : UpsertResult
    {
        $compiled = $this->prepareQuery($query);
        try {
            $result = $this->driver->executeInsert($compiled);
            return UpsertResult::fromInsertResult($result);
        } catch (DuplicateEntryException $dex) {
            return $this->executeUpsertByRow($query);
        }
        return $result;
    }

    public function executeUpsertByRow(UpsertQuery $query)
    {
        $rows = $query->getValues();
        $inserts = 0;
        $updates = 0;
        $firstId = null;
        if (empty($rows)) {
            throw new LogicException('Empty rows set.');
        }
        $keys = $query->getKeys();
        $keyName = count($keys) === 1 ? reset($keys) : null;
        foreach ($rows as $i => $row) {
            $upsert = new UpsertQuery($query->getTable(), $row, $query->getKeys(), $query->getColumns());
            $result = $this->executeUpsertRow($upsert);
            if ($result instanceof InsertResult) {
                $firstId = $i === 0 ? $result->getFirstId() : $firstId;
                $inserts += $result->getNumRows();
            } elseif ($result instanceof UpdateResult) {
                $firstId = $i === 0 && isset($keyName) ? $row[$keyName] : $firstId;
                $updates += $result->getNumRows();
            }
        }
        return UpsertResult::fromResult($result, $inserts, $updates, $firstId);
    }

    private function executeUpsertRow(UpsertQuery $query)
    {
        try {
            $compiled = $this->prepareQuery($query);
            return $this->driver->executeInsert($compiled);
        } catch (DuplicateEntryException $dex) {
            $compiled = $this->prepareQuery($query->getUpdateQuery());
            return $this->driver->executeUpdate($compiled);
        }
    }

    public function executeDelete(DeleteQuery $query) : DeleteResult
    {
        $compiled = $this->prepareQuery($query);
        $result = $this->driver->executeDelete($compiled);
        return $result;
    }

    /**
     * Starts a transaction.
     *
     * @return bool
     * @throws TransactionException if driver doesn't support transacitions or
     * already started.
     */
    public function startTransaction()
    {
        return $this->driver->startTransaction();
    }

    /**
     * Rollbacks actions since transaction starts.
     *
     * @return bool
     * @throws TransactionException if no transaction is running.
     */
    public function rollback()
    {
        return $this->driver->rollback();
    }

    /**
     * Commits actions on transaction.
     *
     * @return bool
     * @throws TransactionException if no transaction is running.
     */
    public function commit()
    {
        return $this->driver->commit();
    }
}