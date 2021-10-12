Connect to a database
=======================================

> To use this feature you require to have proper driver package for your SQL
> database engine, some common drivers are:
>
> - MySQL [francerz/mysql-builder](https://packagist.org/packages/francerz/mysql-builder)
> - Microsoft SQL Server [francerz/sqlsrv-builder](https://packagist.org/packages/francerz/sqlsrv-builder)

Connect using `ConnectParams` object
---------------------------------------
To connect to a Database you might use database parameters by giving access

### Example
```php
use Francerz\SqlBuilder\ConnectParams;
use Francerz\SqlBuilder\DriverManager;
use Francerz\SqlBuilder\DatabaseManager;

// Gets MySQL driver.
$driver = DriverManager::getDriver('mysql');

// Define connection params.
$host       = 'localhost';
$user       = 'root';
$password   = 'a1b2c3';
$database   = 'test';
$port       = 3306;
$encoding   = 'utf8';

// Create connection params.
$params = new ConnectParams($driver, $host, $user, $password, $database, $port, $encoding);

// Create database connection from `ConnectParams` object.
$db = DatabaseManager::connect($params);
```

Connect using URL string
---------------------------------------

Connection might be using a URL string with the following structure:

`{driver}://{user}:{password}@{host}:{port}/{database}`

### Example
```php
use Francerz\SqlBuilder\DatabaseManager;

$url = 'mysql://root:a1b2c3@localhost:3306/test';
$db = DatabaseManager::connect($url);
```

Connect using $_ENV variables
---------------------------------------

Database connection might be done by values at `$_ENV` global php variable.

> To load $_ENV variables from `.env` files you might need a package like
> [vlucas/phpdotenv](https://packagist.org/packages/vlucas/phpdotenv)

### Example

File `.env`
```env
DATABASE_TEST_DRIVER = 'mysql'
DATABASE_TEST_HOST = 'localhost'
DATABASE_TEST_PORT = 3306
DATABASE_TEST_USER = 'root'
DATABASE_TEST_PSWD = 'a1b2c3'
DATABASE_TEST_NAME = 'test'
DATABASE_TEST_ENCD = 'utf8'
```

PHP script:
```php
use Francerz\SqlBuilder\DatabaseManager;

$db = DatabaseManager::connect('test');
```
