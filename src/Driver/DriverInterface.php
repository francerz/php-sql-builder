<?php

namespace Francerz\SqlBuilder\Driver;

use Francerz\SqlBuilder\CompiledQuery;
use Francerz\SqlBuilder\ConnectParams;
use Francerz\SqlBuilder\Results\DeleteResult;
use Francerz\SqlBuilder\Results\InsertResult;
use Francerz\SqlBuilder\Results\QueryResultInterface;
use Francerz\SqlBuilder\Results\SelectResult;
use Francerz\SqlBuilder\Results\UpdateResult;

interface DriverInterface
{
    public function connect(ConnectParams $params);
    public function getCompiler() : ?QueryCompilerInterface;
    public function getTranslator() : ?QueryTranslatorInterface;
    
    public function executeSelect(CompiledQuery $query) : SelectResult;
    public function executeInsert(CompiledQuery $query) : InsertResult;
    public function executeUpdate(CompiledQuery $query) : UpdateResult;
    public function executeDelete(CompiledQuery $query) : DeleteResult;
}