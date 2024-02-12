SQL Builder
=======================================

![Packagist](https://img.shields.io/packagist/v/francerz/sql-builder)
![Build Status](https://github.com/francerz/php-sql-builder/workflows/PHP%20Unit%20Tests/badge.svg)
![Packagist Downloads](https://img.shields.io/packagist/dt/francerz/sql-builder)
![License](https://img.shields.io/github/license/francerz/php-sql-builder?color=%230080FF)

A query builder that allows optimal performance object based query construction.

Table of contents
---------------------------------------
- [SQL Builder](#sql-builder)
  - [Table of contents](#table-of-contents)
  - [Installation ↑](#installation-)
  - [Connect to database ↑](#connect-to-database-)
  - [Basic common usage syntax ↑](#basic-common-usage-syntax-)
    - [Select query ↑](#select-query-)
    - [Insert query ↑](#insert-query-)
    - [Update query ↑](#update-query-)
    - [Delete query ↑](#delete-query-)
  - [Build SELECT with WHERE or HAVING clause  ↑](#build-select-with-where-or-having-clause--)
      - [List of operators ↑](#list-of-operators-)
  - [Building SELECT with JOIN  ↑](#building-select-with-join--)
    - [SUPPORTED JOIN TYPES](#supported-join-types)
    - [Examples](#examples)
  - [SELECT nesting  ↑](#select-nesting--)
  - [Transactions ↑](#transactions-)
  - [Executing Stored Procedures  ↑](#executing-stored-procedures--)

Installation [↑](#table-of-contents)
---------------------------------------

This package can be installed with composer using following command.

```bash
composer require francerz/sql-builder
```

Connect to database [↑](#table-of-contents)
---------------------------------------

Using an URI string
```php
$db = DatabaseManager::connect('driver://user:password@host:port/database');
```

Using $_ENV global variable
```php
$_ENV['DATABASE_SCHOOL_DRIVER'] = 'driver';
$_ENV['DATABASE_SCHOOL_HOST'] = 'host';
$_ENV['DATABASE_SCHOOL_PORT'] = 'port';
$_ENV['DATABASE_SCHOOL_USER'] = 'user';
$_ENV['DATABASE_SCHOOL_PSWD'] = 'password';
$_ENV['DATABASE_SCHOOL_NAME'] = 'database';

// Support to Docker secrets
$_ENV['DATABASE_SCHOOL_PSWD_FILE'] = '/run/secrets/db_school_password';

$db = DatabaseManager::connect('school');
```



Basic common usage syntax [↑](#table-of-contents)
---------------------------------------
```php
class Group {
    public $group_id;
    public $subject;
    public $teacher;
}
```

### Select query [↑](#table-of-contents)
```php
// SELECT group_id, subject, teacher FROM groups
$query = Query::selectFrom('groups', ['group_id', 'subject', 'teacher']);

$db = DatabaseManager::connect('school');
$result = $db->executeSelect($query);
$groups = $result->toArray(Group::class);
```

### Insert query [↑](#table-of-contents)
```php
$group = new Group();
$group->subject = 'Database fundamentals';
$group->teacher = 'francerz';

// INSERT INTO groups (subject, teacher) VALUES ('Database fundamentals', 'francerz')
$query = Query::insertInto('groups', $group, ['subject', 'teacher']);

$db = DatabaseManager::connect('school');
$result = $db->executeInsert($query);
$group->group_id = $result->getInsertedId();
```

### Update query [↑](#table-of-contents)
```php
$group = new Group();
$group->group_id = 10;
$group->subject = 'Introduction to databases';

// UPDATE groups SET subject = 'Introduction to databases' WHERE group_id = 10
$query = Query::update('groups', $group, ['group_id'], ['subject']);

$db = DatabaseManager::connect('school');
$result = $db->executeUpdate($query);
```

### Delete query [↑](#table-of-contents)
```php
// DELETE FROM groups WHERE group_id = 10
$query = Query::deleteFrom('groups', ['group_id' => 10]);

$db = DatabaseManager::connect('school');
$result = $db->executeDelete($query);
```

---

Build SELECT with WHERE or HAVING clause  [↑](#table-of-contents)
---------------------------------------

Bellow are examples of using `WHERE` clause which aplies to `SELECT`, `UPDATE`
and `DELETE` queries.

Selecting all fields from table `groups` when the value of column `group_id` is
equal to `10`.

```sql
SELECT * FROM groups WHERE group_id = 10
```
```php
// Explicit syntax
$query = Query::selectFrom('groups')->where()->equals('group_id', 10);

// Implicit syntax
$query = Query::selectFrom('groups')->where('group_id', 10);
```

---

Selecting all fields from table `groups` when value of column `group_id` is
equals to `10`, `20` or `30`.

```sql
SELECT * FROM groups WHERE group_id IN (10, 20, 30)
```
```php
// Explicit syntax
$query = Query::selectFrom('groups')->where()->in('group_id', [10, 20, 30]);

// Implicit syntax
$query = Query::selectFrom('groups')->where('group_id', [10, 20, 30]);
```

---

Selecting all fields from table `groups`when value of column `teacher` is
`NULL`.

```sql
SELECT * FROM groups WHERE teacher IS NULL
```
```php
// Explicit syntax
$query = Query::selectFrom('groups')->where()->null('teacher');

// Implicit compact syntax
$query = Query::selectFrom('groups')->where('teacher', 'NULL');
```

---

Selecting all fields from table `groups` when value of column `group_id` is
less or equals to `10` and value from column `subject` contains the word
`"database"`.

```sql
SELECT * FROM groups WHERE group_id <= 10 AND subject LIKE '%database%'
```
```php
// Explicit syntax
$query = Query::selectFrom('groups');
$query->where()->lessEquals('group_id', 10)->andLike('subject', '%database%');

// Implicit compact syntax
$query = Query::selectFrom('groups');
$query->where('group_id', '<=', 10)->andLike('subject', '%database%');
```

---

Selecting all fields from table `groups` when the value of `group_id` is equals
to `10` or is within the range from `20` to `30`.

```sql
SELECT * FROM groups WHERE (group_id = 10 OR group_id BETWEEN 20 AND 30)
```
```php
$query = Query::selectFrom('groups');

// Using an anonymous function to emulate parenthesis
$query->where(function(ConditionList $subwhere) {
    $subwhere
        ->equals('group_id', 10)
        ->orBetween('group_id', 20, 30);
});
```

Parenthesis anonymous function only works in the following syntax.
- `$query->where(function)`
- `$query->where()->not(function)`
- `$query->where()->and(function)`
- `$query->where()->or(function)`
- `$query->where()->andNot(function)`
- `$query->where()->orNot(function)`

---

#### List of operators [↑](#table-of-contents)

The library has a complete list of operators that are mostly common to every SQL
database engine and to facilitate reading, also prefixes the `and` and `or`
logical operators.

| Operator      | Regular (AND)                 | AND                              | OR                              |
| ------------- | ----------------------------- | -------------------------------- | ------------------------------- |
| `=`           | `equals($op1, $op2)`          | `andEquals($op1, $op2)`          | `orEquals($op1, $op2)`          |
| `<>` or `!=`  | `notEquals($op1, $op2)`       | `andNotEquals($op1, $op2)`       | `orNotEquals($op1, $op2)`       |
| `<`           | `lessThan($op1, $op2)`        | `andLessThan($op1, $op2)`        | `orLessthan($op1, $op2)`        |
| `<=`          | `lessEquals($op1, $op2)`      | `andLessEquals($op1, $op2)`      | `orLessEquals($op1, $op2)`      |
| `>`           | `greaterThan($op1, $op2)`     | `andGreaterThan($op1, $op2)`     | `orGreaterThan($op1, $op2)`     |
| `>=`          | `greaterEquals($op1, $op2)`   | `andGreaterEquals($op1, $op2)`   | `orGreaterEquals($op1, $op2)`   |
| `LIKE`        | `like($op1, $op2)`            | `andLike($op1, $op2)`            | `orLike($op1, $op2)`            |
| `NOT LIKE`    | `notLike($op1, $op2)`         | `andNotLike($op1, $op2)`         | `orNotLike($op1, $op2)`         |
| `IS NULL`     | `null($op)`                   | `andNull($op)`                   | `orNull($op)`                   |
| `IS NOT NULL` | `notNull($op)`                | `andNotNull($op)`                | `orNotNull($op)`                |
| `BETWEEN`     | `between($op, $min, $max)`    | `andBetween($op, $min, $max)`    | `orBetween($op, $min, $max)`    |
| `NOT BETWEEN` | `notBetween($op, $min, $max)` | `andNotBetween($op, $min, $max)` | `orNotBetween($op, $min, $max)` |
| `IN`          | `in($op, $array)`             | `andIn($op, $array)`             | `orIn($op, $array)`             |
| `NOT IN`      | `notIn($op, $array)`          | `andNotIn($op, $array)`          | `orNotIn($op, $array)`          |

> **About `ConditionList` class**
>
> The examples of condition list, functions and operators applies in the same
> way to `WHERE`, `HAVING` and `ON` clauses.

---

Building SELECT with JOIN  [↑](#table-of-contents)
---------------------------------------

One of the most common operations in relational databases is merging and
combining data from multiple tables. The join operations allow to combine the
data from multiple tables by using the `INNER JOIN`, `LEFT JOIN`, `RIGHT JOIN`
and `CROSS JOIN` syntax.

### SUPPORTED JOIN TYPES

Query Builder supports many types of `JOIN` syntaxes:

```php
// INNER JOIN
$query->innerJoin($table, $columns = []);

// CROSS JOIN
$query->crossJoin($table, $columns = []);

// LEFT JOIN
$query->leftJoin($table, $columns = []);

// RIGHT JOIN
$query->rightJoin($table, $columns = []);
```

> **SQL Join Syntax Compatibility:**
>
> Join Syntax is available to `SELECT`, `UPDATE` and `DELETE` sql syntax,
> however, not all database engines might support it.

### Examples

```sql
SELECT * FROM groups INNER JOIN teachers ON groups.teacher_id = teachers.teacher_id
```
```php
$query = Query::selectFrom('groups');
$query
    ->innerJoin('teachers')
    ->on('groups.teacher_id', 'teachers.teacher_id');
```

---

Using table aliases to reduce naming lenght.  
```sql
SELECT * FROM groups AS g INNER JOIN teachers AS t ON g.teacher_id = t.teacher_id
```
```php
// Alias array syntax
$query = Query::selectFrom(['g' => 'groups']);
$query
    ->innerJoin(['t' => 'teachers'])
    ->on('g.teacher_id', 't.teacher_id');

// Alias "AS" string syntax
$query = Query::selectFrom('groups AS g');
$query
    ->innerJoin('teachers AS t')
    ->on('g.teacher_id', 't.teacher_id');
```

---

Multiple database (same host) select with join.  
```sql
SELECT * FROM school.groups AS g INNER JOIN hr.employees AS e ON g.teacher_id = e.employee_id
```
```php
$query = Query::selectFrom('school.groups AS g');
$query
    ->innerJoin('hr.employees AS e')
    ->on('g.teacher_id','e.employee_id');
```

---

Selecting fields from joined tables
```sql
SELECT g.group_id, t.given_name, t.family_name
FROM groups AS g
INNER JOIN teachers AS t ON g.teacher_id = t.teacher_id
```
```php
$query = Query::selectFrom('groups AS g', ['group_id']);
$query
    ->innerJoin('teachers AS t', ['given_name', 'family_name'])
    ->on('g.teacher_id', 't.teacher_id');
```

---

Renaming fields from joined tables
```sql
SELECT g.group_id, CONCAT(t.given_name, ' ', t.family_name) AS teacher_name
FROM groups AS g
INNER JOIN teachers AS t ON g.teacher_id = t.teacher_id
```
```php
$query = Query::selectFrom('groups AS g', ['group_id']);
$query
    ->innerJoin('teachers AS t', [
        'teacher_name' => "CONCAT(t.given_name, ' ', t.family_name)"
    ])->on('g.teacher_id', 't.teacher_id');
```

---

Selecting columns into an external function (cleaner code)
```sql
SELECT g.group_id, CONCAT(t.given_name, ' ', t.family_name) AS teacher_name
FROM groups AS g
INNER JOIN teachers AS t ON g.teacher_id = t.teacher_id
```
```php
$query = Query::selectFrom('groups AS g');
$query
    ->innerJoin('teachers AS t')
    ->on('g.teacher_id', 't.teacher_id');
$query->columns([
    'g.group_id',
    'teacher_name' => "CONCAT(t.given_name, ' ', t.family_name)"
]);
```

---

Join tables and subqueries
```sql
-- Gets all groups of active teachers
SELECT g.group_id, CONCAT(t.given_name, ' ', t.family_name) AS teacher_name
FROM groups AS g
INNER JOIN (SELECT * FROM teachers WHERE active = 1) AS t
ON g.teacher_id = t.teacher_id
```
```php
// Creating subquery object
$subquery = Query::selectFrom('teachers');
$subquery->where('active', 1);

$query = Query::selectFrom('groups AS g');
$query
    ->innerJoin(['t' => $subquery])
    ->on('g.teacher_id', 't.teacher_id');
$query->columns([
    'g.group_id',
    'teacher_name' => "CONCAT(t.given_name, ' ', t.family_name)"
]);
```

SELECT nesting  [↑](#table-of-contents)
---------------------------------------

Sometimes database table joining might not be enought for all the data requirements.
Is quite often that for each row in a result of a query, another filtered result
query must be executed.

This scenario produces excesive complex code to nest each result by each row.
Also impacts performance by increasing the loops and database access roundtrips.
For this reason there's a syntax that creates the most lightweight and efficient
way to query nested data, preventing access overhead and reducing processing time.

```php
// Students query
$studentsQuery = Query::selectFrom(
    'students',
    ['student_id', 'group_id', 'first_name', 'last_name']
);

// Groups query
$groupsQuery = Query::selectFrom(
    'groups',
    ['group_id', 'subject', 'teacher']
);

// Nesting students by each group
$groupsQuery->nest(['Students' => $studentsQuery], function (NestedSelect $nest, RowProxy $row) {
    $nest->getSelect()->where('s.group_id', $row->group_id);
});

$db = DatabaseManager::connect('school');
$result = $db->executeSelect($groupsQuery);
$groups = $result->toArray();
```

Result would be like this:
```json
[
    {
        "group_id": 1,
        "subject": "Programing fundamentals",
        "teacher": "Rosemary",
        "Students": [
            {
                "student_id": 325,
                "first_name": "Charlie",
                "last_name": "Ortega"
            },
            {
                "student_id": 743,
                "first_name": "Beth",
                "last_name": "Wilson"
            }
        ]
    },
    {
        "group_id": 2,
        "subject" : "Object Oriented Programming",
        "teacher": "Steve",
        "Students": [
            {
                "student_id": 536,
                "first_name": "Dylan",
                "last_name": "Morrison"
            }
        ]
    }
]
```

Transactions [↑](#table-of-contents)
---------------------------------------

One of the most important features in databases is to keep data consistency
across multiple records that might be stored in multiple tables.

```php
$db = DatabaseManager::connect('school');
try {
    $db->startTransaction();
    
    // Perform any needed operation inside this block to keep consistency.
    
    $db->commit();
} catch (Exception $ex) {
    $db->rollback();
}
```


Executing Stored Procedures  [↑](#table-of-contents)
---------------------------------------

```php
// Connecting to database 'school'.
$db = DatabaseManager::connect('school');

// Calls stored procedure with two argments.
/** @var SelectResult[] */
$results = $db->call('procedure_name', 'arg1', 'arg2');

// Shows how many results obtained from procedure.
echo count($results) . ' results.' . PHP_EOL;

// Iterating procedure result sets.
foreach ($results as $i => $selectResult) {
    echo "Fetched " . $selectResult->getNumRows() . PHP_EOL;
}
```
