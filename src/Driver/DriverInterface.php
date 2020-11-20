<?php

namespace Francerz\SqlBuilder\Driver;

use Francerz\SqlBuilder\CompiledQuery;
use Francerz\SqlBuilder\ConnectParams;
use Francerz\SqlBuilder\Results\QueryResultInterface;

interface DriverInterface
{
    public function connect(ConnectParams $params);
    public function getCompiler() : ?QueryCompilerInterface;
    public function getTranslator() : ?QueryTranslatorInterface;
    public function execute(CompiledQuery $query) : QueryResultInterface;
}