<?php

namespace Francerz\SqlBuilder\Components;

use Francerz\SqlBuilder\Expressions\ComparableComponentInterface;

class SqlRaw implements ComparableComponentInterface
{
    private $content;
    public function __construct($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function __toString()
    {
        return $this->content;
    }
}