<?php

namespace Francerz\SqlBuilder;

use Francerz\PowerData\Index;
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
use Francerz\SqlBuilder\Results\UpsertResult;
use Francerz\SqlBuilder\Tools\QueryOptimizer;
use InvalidArgumentException;
use LogicException;

class DatabaseHandler
{
    private $compiler;
    private $driver;

    private $nestTranslator;
    private $nestMerger;

    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
        $this->compiler = $driver->getCompiler() ?? new QueryCompiler();
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

    /**
     * @deprecated v0.2.64 Use executeSelect, executeInsert, executeUpdate or executeDelete instead.
     */
    public function execute(QueryInterface $query): QueryResultInterface
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

    public function executeSelect(SelectQuery $query): SelectResult
    {
        $query = QueryOptimizer::optimizeSelect($query);
        $compiled = $this->compiler->compileSelect($query);
        $result = $this->driver->executeSelect($compiled);

        if (count($result) === 0) {
            return $result;
        }

        foreach ($query->getNests() as $nest) {
            if (!$nest instanceof Nest) {
                throw new LogicException('Invalid nest value');
            }
            $nestSelect = $nest->getNested()->getSelect();
            $nestConn = $this;
            if ($nestSelect->getConnection() !== null) {
                $nestConn = DatabaseManager::connect($nestSelect->getConnection());
            }
            $nestTranslation = $nestConn->nestTranslator->translate($nestSelect, $result);
            $nestResult = $nestConn->executeSelect($nestTranslation);
            $this->nestMerger->merge($result, $nestResult, $nest);
        }

        foreach ($query->getAfterExecuteActions() as $action) {
            $action($result);
        }

        return $result;
    }

    public function executeInsert(InsertQuery $query): InsertResult
    {
        $compiled = $this->compiler->compileInsert($query);
        $result = $this->driver->executeInsert($compiled);
        return $result;
    }

    public function executeUpdate(UpdateQuery $query): UpdateResult
    {
        $compiled = $this->compiler->compileUpdate($query);
        $result = $this->driver->executeUpdate($compiled);
        return $result;
    }

    public function executeDelete(DeleteQuery $query): DeleteResult
    {
        $compiled = $this->compiler->compileDelete($query);
        $result = $this->driver->executeDelete($compiled);
        return $result;
    }

    /**
     * Executes stored procedure.
     *
     * @param StoredProcedure $procedure
     *
     * @return SelectResult[]
     */
    public function executeProcedure(StoredProcedure $procedure)
    {
        $compiled = $this->compiler->compileProcedure($procedure);
        return $this->driver->executeProcedure($compiled);
    }

    /**
     * Inserts or updates rows as needed, based on its presence on database.
     *
     * If row not exists on current table it will be inserted.
     * If row exists on current table it will be updated.
     *
     * @param UpsertQuery $query
     * @return UpsertResult
     */
    public function executeUpsert(UpsertQuery $query): UpsertResult
    {
        if (count($query) === 0) {
            return new UpsertResult();
        }

        $keys = $query->getKeys();
        $keys = array_combine($keys, $keys);

        // Finds all rows that can match.
        $index = new Index($query, $keys);
        $selectQuery = Query::selectFrom($query->getTable());
        foreach ($index->getColumns() as $c) {
            $selectQuery->where($c, $index->getColumnValues($c));
        }
        $selectResult = $this->executeSelect($selectQuery);

        // Checks if found rows match with upserted rows.
        $inserts = [];
        $updates = [];
        $index = new Index($selectResult, $keys);
        foreach ($query as $row) {
            $rowA = (array)$row;
            $filter = array_intersect_key($rowA, $keys);
            $matches = $index[$filter];
            if (count($matches) === 0) {
                $inserts[] = $rowA;
                continue;
            }
            if (array_search($row, $matches) === false) {
                $updates[] = $rowA;
                continue;
            }
        }

        // Performs inserts and updates as needed.
        $numInserts = 0;
        $numUpdates = 0;
        $insertedId = null;
        $success = true;
        if (!empty($inserts)) {
            $insertQuery = Query::insertInto($query->getTable(), $inserts);
            $insertResult = $this->executeInsert($insertQuery);
            $numInserts += $insertResult->getNumRows();
            $insertedId = $insertResult->getInsertedId();
            $success &= $insertResult->success();
        }
        if (!empty($updates)) {
            foreach ($updates as $u) {
                $updateQuery = Query::update($query->getTable(), $u, $query->getKeys(), $query->getColumns());
                $updateResult = $this->executeUpdate($updateQuery);
                $numUpdates += $updateResult->getNumRows();
                $success &= $updateResult->success();
            }
        }
        return new UpsertResult($numInserts, $numUpdates, $insertedId, true);
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

    /**
     * Checks if current connection is on an active transaction.
     *
     * @return bool Returns TRUE if connection is on an active transaction,
     * returns FALSE otherwise.
     */
    public function inTransaction()
    {
        return $this->driver->inTransaction();
    }
}
