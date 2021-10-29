<?php

namespace Francerz\SqlBuilder;

use Francerz\SqlBuilder\Components\Table;

interface QueryInterface
{
    public function getTable(): Table;

    /**
     * Sets default connection database for current query.
     *
     * This parameter will be passed to DatabaseManager to connect to given
     * database.
     *
     * If is null, default connection will be used.
     *
     * @param string|UriInterface|ConnectParams|null $database
     * @return void
     */
    public function setDatabase($database);

    /**
     * Retrieves assigned database to query.
     *
     * @return string|UriInterface|ConnectParams|null
     */
    public function getDatabase();
}
