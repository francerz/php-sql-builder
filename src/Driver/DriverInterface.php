<?php

namespace Francerz\SqlBuilder\Driver;

use Francerz\SqlBuilder\CompiledQuery;
use Francerz\SqlBuilder\ConnectParams;
use Francerz\SqlBuilder\Results\DeleteResult;
use Francerz\SqlBuilder\Results\InsertResult;
use Francerz\SqlBuilder\Results\SelectResult;
use Francerz\SqlBuilder\Results\UpdateResult;

interface DriverInterface
{
    public function connect(ConnectParams $params);
    public function getCompiler() : ?QueryCompilerInterface;
    public function getTranslator() : ?QueryTranslatorInterface;
    
    /**
     * Executes given SELECT CompiledQuery and returns the result.
     *
     * @param CompiledQuery $query Compiled SELECT query.
     * @return SelectResult
     * 
     * @throws ExecuteSelectException
     */
    public function executeSelect(CompiledQuery $query) : SelectResult;

    /**
     * Executes given INSERT CompiledQuery and returns the result.
     *
     * @param CompiledQuery $query Compiled INSERT query.
     * @return InsertResult
     * 
     * @throws ExecuteInsertException
     */
    public function executeInsert(CompiledQuery $query) : InsertResult;

    /**
     * Executees given UPDATE CompiledQuery and returns the result.
     *
     * @param CompiledQuery $query
     * @return UpdateResult
     * 
     * @throws ExecuteUpdateException
     */
    public function executeUpdate(CompiledQuery $query) : UpdateResult;

    /**
     * Executes given DELETE CompiledQuery and returns the result.
     *
     * @param CompiledQuery $query
     * @return DeleteResult
     * 
     * @throws ExecuteDeleteException
     */
    public function executeDelete(CompiledQuery $query) : DeleteResult;
}