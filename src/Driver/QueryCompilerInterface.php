<?php

namespace Francerz\SqlBuilder\Driver;

use Francerz\SqlBuilder\Compiles\CompiledDelete;
use Francerz\SqlBuilder\Compiles\CompiledInsert;
use Francerz\SqlBuilder\Compiles\CompiledProcedure;
use Francerz\SqlBuilder\Compiles\CompiledSelect;
use Francerz\SqlBuilder\Compiles\CompiledUpdate;
use Francerz\SqlBuilder\DeleteQuery;
use Francerz\SqlBuilder\InsertQuery;
use Francerz\SqlBuilder\SelectQuery;
use Francerz\SqlBuilder\StoredProcedure;
use Francerz\SqlBuilder\UpdateQuery;

interface QueryCompilerInterface
{
    public function compileSelect(SelectQuery $query): CompiledSelect;
    public function compileInsert(InsertQuery $query): CompiledInsert;
    public function compileUpdate(UpdateQuery $query): CompiledUpdate;
    public function compileDelete(DeleteQuery $query): CompiledDelete;
    public function compileProcedure(StoredProcedure $procedure): ?CompiledProcedure;
}
