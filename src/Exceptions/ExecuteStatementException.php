<?php

namespace Francerz\SqlBuilder\Exceptions;

use Exception;
use Francerz\SqlBuilder\Compiles\AbstractCompiledStatement;
use Throwable;

class ExecuteStatementException extends Exception
{
    private $compiledStatement;

    public function __construct(
        AbstractCompiledStatement $compiledStatement,
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message .= PHP_EOL . $compiledStatement->getQuery();
        $message .= PHP_EOL . print_r($compiledStatement->getValues(), true);
        parent::__construct($message, $code, $previous);
        $this->compiledStatement = $compiledStatement;
    }

    public function getCompiledStatement()
    {
        return $this->compiledStatement;
    }

    public function getStatement()
    {
        return $this->compiledStatement->getQuery();
    }

    public function getValues()
    {
        return $this->compiledStatement->getValues();
    }
}
