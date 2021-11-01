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
     * @param string|UriInterface|ConnectParams|DatabaseHandler|null $database
     * @return void
     */
    public function setConnection($connectiom);

    /**
     * Retrieves assigned connection to query.
     *
     * @return string|UriInterface|ConnectParams|DatabaseHandler|null
     */
    public function getConnection();
}
