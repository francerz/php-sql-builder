SELECT Query
=======================================

The most common SQL query and the most versatile, used to retrieve data from
databases is `SELECT`.

Creating Query
---------------------------------------

To create a Query is convenient to use the `Query` class wich has various static
methods that includes shorts to most common queries.

With the given PHP code.
```php
use Francerz\SqlBuilder\Query;

$query = Query::selectFrom('table');
```

Will compile to following SQL code.
```sql
SELECT * FROM table;
```

### Selecting given columns

Sometimes is not required to retrieve every column from selected table, this
can be acomplished by providing an array with selected columns.

PHP code:
```php
$query = Query::selectFrom('table', ['col1','col2','col3']);
```

SQL code:
```sql
SELECT col1, col2, col3 FROM table;
```

### Filtering data

The select query alone will retrieve every row at table, which is mostly
undesired. By this mean, exists the `WHERE` clause that filters data by setting
conditions.

To use `WHERE` clause it can be accessed by the `where()` method in `SelectQuery`
this is a `ConditionList` element that contains multiple functions to filter data.

PHP code:
```php
$query = Query::selectFrom('table', ['col1', 'col2', 'col3']);
$query->where()->equals('col1', 10);
```

SQL code:
```sql
SELECT col1, col2, col3 FROM col1 = 10;
```

Having multiple conditions can be connected by chaining method calls over
the `ConditionList` object.

```php
$query->where()->equals('col1', 10)->andLessThan('col2', 1000);
```
```sql
WHERE col1 = 10 AND col2 < 1000
```
