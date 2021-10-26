<?php

namespace Francerz\SqlBuilder\Dev\Driver;

use Francerz\SqlBuilder\CompiledQuery;
use Francerz\SqlBuilder\ConnectParams;
use Francerz\SqlBuilder\Driver\DriverInterface;
use Francerz\SqlBuilder\Driver\QueryCompilerInterface;
use Francerz\SqlBuilder\Driver\QueryTranslatorInterface;
use Francerz\SqlBuilder\DriverManager;
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

    public function getTranslator(): ?QueryTranslatorInterface
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

    public function executeSelect(CompiledQuery $query): SelectResult
    {
        return new SelectResult(new CompiledQuery(''), []);
    }

    public function executeInsert(CompiledQuery $query): InsertResult
    {
        return new InsertResult(new CompiledQuery(''));
    }

    public function executeUpdate(CompiledQuery $query): UpdateResult
    {
        return new UpdateResult(new CompiledQuery(''));
    }

    public function executeDelete(CompiledQuery $query): DeleteResult
    {
        return new DeleteResult(new CompiledQuery(''));
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
