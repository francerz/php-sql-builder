<?php

namespace Francerz\SqlBuilder\Dev\Driver;

use Francerz\SqlBuilder\Compiles\CompiledDelete;
use Francerz\SqlBuilder\Compiles\CompiledInsert;
use Francerz\SqlBuilder\Compiles\CompiledProcedure;
use Francerz\SqlBuilder\Compiles\CompiledSelect;
use Francerz\SqlBuilder\Compiles\CompiledUpdate;
use Francerz\SqlBuilder\ConnectParams;
use Francerz\SqlBuilder\Driver\DriverInterface;
use Francerz\SqlBuilder\Driver\QueryCompilerInterface;
use Francerz\SqlBuilder\Results\DeleteResult;
use Francerz\SqlBuilder\Results\InsertResult;
use Francerz\SqlBuilder\Results\SelectResult;
use Francerz\SqlBuilder\Results\UpdateResult;

class TestDriver implements DriverInterface
{
    public function connect(ConnectParams $params)
    {
    }

    public function getCompiler(): ?QueryCompilerInterface
    {
        return null;
    }

    public function getDefaultHost(): string
    {
        return 'localhost';
    }

    public function getDefaultPort(): int
    {
        return 0;
    }

    public function getDefaultUser(): string
    {
        return '';
    }

    public function getDefaultPswd(): string
    {
        return '';
    }

    public function executeSelect(CompiledSelect $query): SelectResult
    {
        return new SelectResult([]);
    }

    public function executeInsert(CompiledInsert $query): InsertResult
    {
        return new InsertResult();
    }

    public function executeUpdate(CompiledUpdate $query): UpdateResult
    {
        return new UpdateResult();
    }

    public function executeDelete(CompiledDelete $query): DeleteResult
    {
        return new DeleteResult();
    }

    public function executeProcedure(CompiledProcedure $query): array
    {
        return [];
    }

    public function inTransaction(): bool
    {
        return false;
    }

    public function startTransaction(): bool
    {
        return false;
    }

    public function commit(): bool
    {
        return false;
    }

    public function rollback(): bool
    {
        return false;
    }
}
