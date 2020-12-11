<?php

namespace Francerz\SqlBuilder\Exceptions;

use Exception;
use Francerz\SqlBuilder\CompiledQuery;
use Throwable;

class ExecuteQueryException extends Exception
{
    private $compiledQuery;

    public function __construct(CompiledQuery $compiledQuery, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->compiledQuery = $compiledQuery;
    }

    public function getCompiledQuery()
    {
        return $this->compiledQuery;
    }

    public function getQueryString()
    {
        return $this->compiledQuery->getQuery();
    }

    public function getQueryObject()
    {
        return $this->compiledQuery->getObject();
    }

    public function getValues()
    {
        return $this->compiledQuery->getValues();
    }
}