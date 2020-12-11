<?php

namespace Francerz\SqlBuilder\Components;

use Francerz\SqlBuilder\Expressions\ComparableComponentInterface;
use Francerz\SqlBuilder\Query;

class SqlFunction implements ComparableComponentInterface
{
    private $name;
    private $args;

    public function __construct($name, array $args = [])
    {
        $this->name = $name;
        $this->setArgs($args);
    }

    private function setArgs(array $args)
    {
        $this->args = [];
        foreach ($args as $arg) {
            if ($arg instanceof ComparableComponentInterface) {
                $this->args[] = $arg;
                continue;
            }
            $this->args[] = Query::value($arg);
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getArgs()
    {
        return $this->args;
    }
}