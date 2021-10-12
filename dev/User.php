<?php

namespace Francerz\SqlBuilder\Dev;

class User
{
    /**
     * @var int
     * @sql-key
     */
    public $user_id;

    /** @var string */
    public $username;

    /** @var string */
    private $password;

    /** @var bool */
    public $enabled;

    /**
     * @var bool
     * @sql-ignore
     */
    public $loggedIn;
}
