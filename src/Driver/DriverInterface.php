<?php

namespace Francerz\SqlBuilder\Driver;

use Francerz\SqlBuilder\Compiles\CompiledDelete;
use Francerz\SqlBuilder\Compiles\CompiledInsert;
use Francerz\SqlBuilder\Compiles\CompiledProcedure;
use Francerz\SqlBuilder\Compiles\CompiledSelect;
use Francerz\SqlBuilder\Compiles\CompiledUpdate;
use Francerz\SqlBuilder\ConnectParams;
use Francerz\SqlBuilder\Exceptions\TransactionException;
use Francerz\SqlBuilder\Results\DeleteResult;
use Francerz\SqlBuilder\Results\InsertResult;
use Francerz\SqlBuilder\Results\SelectResult;
use Francerz\SqlBuilder\Results\UpdateResult;

interface DriverInterface
{
    /**
     * Connects to driver with given parameters.
     *
     * @param ConnectParams $params
     * @return void
     */
    public function connect(ConnectParams $params);

    /**
     * Retrieves a QueryCompilerInstance compatible object.
     *
     * @return QueryCompilerInterface|null
     */
    public function getCompiler(): ?QueryCompilerInterface;

    /**
     * Retrieves a QueryTranslatorInterface compatible object.
     *
     * @return QueryTranslatorInterface|null
     */
    public function getTranslator(): ?QueryTranslatorInterface;

    /**
     * Returns default host address or name for given driver.
     *
     * @return string host name
     */
    public function getDefaultHost(): string;

    /**
     * Returns default port number for given driver.
     *
     * @return integer port number
     */
    public function getDefaultPort(): int;

    /**
     * Returns default user name for given driver.
     *
     * @return string user name
     */
    public function getDefaultUser(): string;

    /**
     * Returns default password string for given driver.
     *
     * @return string password string
     */
    public function getDefaultPswd(): string;

    /**
     * Executes given SELECT CompiledQuery and returns the result.
     *
     * @param CompiledSelect $query Compiled SELECT query.
     * @return SelectResult
     *
     * @throws ExecuteSelectException
     */
    public function executeSelect(CompiledSelect $query): SelectResult;

    /**
     * Executes given INSERT CompiledQuery and returns the result.
     *
     * @param CompiledInsert $query Compiled INSERT query.
     * @return InsertResult
     *
     * @throws ExecuteInsertException
     */
    public function executeInsert(CompiledInsert $query): InsertResult;

    /**
     * Executees given UPDATE CompiledQuery and returns the result.
     *
     * @param CompiledUpdate $query
     * @return UpdateResult
     *
     * @throws ExecuteUpdateException
     */
    public function executeUpdate(CompiledUpdate $query): UpdateResult;

    /**
     * Executes given DELETE CompiledQuery and returns the result.
     *
     * @param CompiledDelete $query
     * @return DeleteResult
     *
     * @throws ExecuteDeleteException
     */
    public function executeDelete(CompiledDelete $query): DeleteResult;

    /**
     * Execute procedure and returns the results.
     *
     * @param CompiledProcedure $query
     * @return SelectResult[]
     *
     * @throws ExecuteProcedureException
     */
    public function executeProcedure(CompiledProcedure $query): array;

    /**
     * Checks if current connection is on an active transaction.
     *
     * @return boolean
     */
    public function inTransaction(): bool;

    /**
     * Starts a transaction on current database connection.
     *
     * @return boolean **TRUE** if sucess, **FALSE** otherwise.
     */
    public function startTransaction(): bool;

    /**
     * Rollbacks a transaction on current database connection.
     *
     * @return boolean **TRUE** if success, **FALSE** otherwise.
     *
     * @throws TransactionException if no transaction is running.
     */
    public function rollback(): bool;

    /**
     * Commits a transaction on current database connection.
     *
     * @return boolean **TRUE** if success, **FALSE** otherwise.
     *
     * @throws TransactionException if no transaction is running.
     */
    public function commit(): bool;
}
