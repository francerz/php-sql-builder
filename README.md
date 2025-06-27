SQL Builder
=======================================

![Packagist](https://img.shields.io/packagist/v/francerz/sql-builder)
![License](https://img.shields.io/github/license/francerz/php-sql-builder?color=%230078D0)
![Packagist Downloads](https://img.shields.io/packagist/dt/francerz/sql-builder?color=%23E0B000)
![Build Status](https://github.com/francerz/php-sql-builder/workflows/PHP%20Unit%20Tests/badge.svg)

A PHP SQL query builder that prioritizes readability and optimal performance
with object based construction.

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
  - [Build SELECT with WHERE or HAVING clause ↑](#build-select-with-where-or-having-clause-)
    - [Parentheses syntax](#parentheses-syntax)
    - [List of operators ↑](#list-of-operators-)
  - [Building SELECT with JOIN ↑](#building-select-with-join-)
    - [SUPPORTED JOIN TYPES](#supported-join-types)
    - [Examples](#examples)
  - [SELECT nesting ↑](#select-nesting-)
    - [Nesting a Collection of Result Objects](#nesting-a-collection-of-result-objects)
    - [Nesting the First or Last Result Object](#nesting-the-first-or-last-result-object)
  - [Transactions ↑](#transactions-)
  - [Executing Stored Procedures ↑](#executing-stored-procedures-)

Installation [↑](#table-of-contents)
---------------------------------------

This package can be installed with composer using following command.

```sh
composer require francerz/sql-builder
```

Connect to database [↑](#table-of-contents)
---------------------------------------

Using an URI string
```php
$db = DatabaseManager::connect('driver://user:password@host:port/database');
```

Using environment variable
```php
putenv('DATABASE_SCHOOL_DRIVER', 'driver');
putenv('DATABASE_SCHOOL_HOST', 'host');
putenv('DATABASE_SCHOOL_INST', 'instanceName');
putenv('DATABASE_SCHOOL_PORT', 'port');
putenv('DATABASE_SCHOOL_USER', 'user');
putenv('DATABASE_SCHOOL_PSWD', 'password');
putenv('DATABASE_SCHOOL_NAME', 'database');

// Support to Docker secrets
putenv('DATABASE_SCHOOL_PSWD_FILE', '/run/secrets/db_school_password');

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

Build SELECT with WHERE or HAVING clause [↑](#table-of-contents)
---------------------------------------

Bellow are examples of using `WHERE` clause which aplies to `SELECT`, `UPDATE`
and `DELETE` queries.

> Selecting all fields from table `groups` when the value of column `group_id` is
> equal to `10`.

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

> Selecting all fields from table `groups` when value of column `group_id` is
> equals to `10`, `20` or `30`.

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

> Selecting all fields from table `groups`when value of column `teacher` is
> `NULL`.

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

> Selecting all fields from table `groups` when value of column `group_id` is
> less or equals to `10` and value from column `subject` contains the word
> `"database"`.

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

### Parentheses syntax

To incorporate highly specific and intricate conditions, it becomes essential to
override the default operator precedence, a task traditionally achieved through
the use of parentheses in SQL syntax. Within the SQL Builder, this functionality
is adeptly handled through the utilization of an anonymous function parameter.

Parentheses anonymous function works in the following syntax:

```php
$query->where(function($subwhere) { });
$query->where->not(function($subwhere) { });
$query->where->and(function($subwhere) { });
$query->where->or(function($subwhere) { });
$query->where->andNot(function($subwhere) { });
$query->where->orNot(function($subwhere) { });
```

> Selecting all fields from table `groups` when the value of `group_id` is
> equals to `10` or is within the range from `20` to `30`.

```sql
SELECT *
    FROM groups
    WHERE subject LIKE '%database%'
    AND (group_id = 10 OR group_id BETWEEN 20 AND 30)
```

```php
$query = Query::selectFrom('groups');

// Using an anonymous function to emulate parenthesis
$query->where()
    ->like('subject', '%database%')
    ->and(function(ConditionList $subwhere) {
        $subwhere
            ->equals('group_id', 10)
            ->orBetween('group_id', 20, 30);
    });
```

---

### List of operators [↑](#table-of-contents)

The library provides a comprehensive array of operators that are largely
consistent across various SQL database engines. To enhance readability, it also
prefixes the `and` and `or` logical operators for clarity.

| SQL Operator  | Regular (AND)                 | AND                              | OR                              |
| ------------- | ----------------------------- | -------------------------------- | ------------------------------- |
| `=`           | `equals($op1, $op2)`          | `andEquals($op1, $op2)`          | `orEquals($op1, $op2)`          |
| `<>` or `!=`  | `notEquals($op1, $op2)`       | `andNotEquals($op1, $op2)`       | `orNotEquals($op1, $op2)`       |
| `<`           | `lessThan($op1, $op2)`        | `andLessThan($op1, $op2)`        | `orLessthan($op1, $op2)`        |
| `<=`          | `lessEquals($op1, $op2)`      | `andLessEquals($op1, $op2)`      | `orLessEquals($op1, $op2)`      |
| `>`           | `greaterThan($op1, $op2)`     | `andGreaterThan($op1, $op2)`     | `orGreaterThan($op1, $op2)`     |
| `>=`          | `greaterEquals($op1, $op2)`   | `andGreaterEquals($op1, $op2)`   | `orGreaterEquals($op1, $op2)`   |
| `LIKE`        | `like($op1, $pattern)`        | `andLike($op1, $pattern)`        | `orLike($op1, $pattern)`        |
| `NOT LIKE`    | `notLike($op1, $pattern)`     | `andNotLike($op1, $pattern)`     | `orNotLike($op1, $pattern)`     |
| `REGEXP`      | `regexp($op1, $pattern)`      | `andRegexp($op1, $pattern)`      | `orRegexp($op1, $pattern)`      |
| `NOT REGEXP`  | `notRegexp($op1, $pattern)`   | `andNotRegexp($op1, $pattern)`   | `orNotRegexp($op1, $pattern)`   |
| `IS NULL`     | `null($op)`                   | `andNull($op)`                   | `orNull($op)`                   |
| `IS NOT NULL` | `notNull($op)`                | `andNotNull($op)`                | `orNotNull($op)`                |
| `BETWEEN`     | `between($op, $min, $max)`    | `andBetween($op, $min, $max)`    | `orBetween($op, $min, $max)`    |
| `NOT BETWEEN` | `notBetween($op, $min, $max)` | `andNotBetween($op, $min, $max)` | `orNotBetween($op, $min, $max)` |
| `IN`          | `in($op, $array)`             | `andIn($op, $array)`             | `orIn($op, $array)`             |
| `NOT IN`      | `notIn($op, $array)`          | `andNotIn($op, $array)`          | `orNotIn($op, $array)`          |
| `IS NOT NULL` | `hasValue($op)`               | `andHasValue($op)`               | `orHasValue($op)`               |

> **About `ConditionList` class**
>
> The examples of condition list, functions and operators applies in the same
> way to `WHERE`, `HAVING` and `ON` clauses.

---

Building SELECT with JOIN [↑](#table-of-contents)
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

Using table aliases to reduce naming length.

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

Selecting fields from joined tables.

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

Renaming fields from joined tables.

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

Selecting columns into an external function (cleaner code).

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

Join tables and subqueries.

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

SELECT nesting [↑](#table-of-contents)
---------------------------------------

In some cases, simple database table joining isn't sufficient for meeting all
data requirements. It's common to need to execute additional filtered queries
for each row in the result of a primary query.

However, this approach often leads to overly complex code and performance issues
due to increased loops and database access roundtrips. To address these
challenges, a more efficient and lightweight syntax is available for querying
nested data.

### Nesting a Collection of Result Objects

The `nestMany` method is used to nest a collection of result objects within each
row of the primary query's result. In the provided example, this is used to
associate multiple students with their respective groups. This approach is
suitable when you expect multiple related records for each main record.

```php
// Primary Query for Groups
$groupsQuery = Query::selectFrom(
    'groups',
    ['group_id', 'subject', 'classroom']
);

// Query for Students
$studentsQuery = Query::selectFrom(
    'students',
    ['student_id', 'group_id', 'first_name', 'last_name']
);

// Nesting students within each group
$groupsQuery
    ->nestMany('Students', $studentsQuery, $groupRow, Student::class)
    ->where('students.group_id', $groupRow->group_id);

// Connecting to the database and executing the query
$db = DatabaseManager::connect('school');
$result = $db->executeSelect($groupsQuery);
$groups = $result->toArray(Group::class);
```

**Result:**

```json
[
    {
        "group_id": 1,
        "subject": "Programing fundamentals",
        "classroom": "A113",
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
        "classroom": "G7-R5",
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

### Nesting the First or Last Result Object

On the other hand, the `linkFirst` method is employed to link only the first
result object from a secondary query with each row of the primary query's
result. In the given code snippet, this is used to link the first teacher to
each group. This method is beneficial when you want to link a single related
record to each main record, prioritizing the first match.

Additionally, there is the `linkLast` method, which is similar to `linkFirst`
but instead links the last result object from a secondary query to each row of
the primary query's result. This can be useful in scenarios where you want to
prioritize the most recent or latest related record for each main record.

```php
// Primary Query for Groups
$groupsQuery = Query::selectFrom(
    'groups',
    ['group_id', 'teacher_id', 'subject', 'classroom']
);

// Query for Teachers
$teachersQuery = Query::selectFrom(
    'teachers',
    ['teacher_id', 'first_name', 'last_name']
);

// Linking the first teacher to each group
$groupsQuery
    ->linkFirst('Teacher', $teachersQuery, $groupRow, Teacher::class)
    ->where('teachers.teacher_id', $groupRow->teacher_id);

// Query for Classes
$classesQuery = Query::selectFrom(
    'groups_classes',
    ['class_id', 'group_id', 'topic', 'date']
)->orderBy('date', 'ASC');

// Linking the last class to each group
$groups
    ->linkLast('LastClass', $classesQuery, $groupRow, GroupClass::class)
    ->where('groups_classes.group_id', $groupRow->group_id);

// Connecting to the database and executing the query
$db = DatabaseManager::connect('school');
$result = $db->executeSelect($groupsQuery);
$groups = $result->toArray(Group::class);
```

**Result:**

```json
[
    {
        "group_id": 1,
        "teacher_id": 3,
        "subject": "Programming fundamentals",
        "classroom": "A113",
        "Teacher": {
            "teacher_id": 3,
            "first_name": "Rosemary",
            "last_name": "Smith"
        },
        "LastClass": {
            "class_id": 233,
            "group_id": 1,
            "topic": "Algorithms",
            "date": "2024-04-18"
        }
    },
    {
        "group_id": 2,
        "teacher_id": 75,
        "subject" : "Object Oriented Programming",
        "classroom": "G7-R5",
        "Teacher": {
            "teacher_id": 75,
            "first_name": "Steve",
            "last_name": "Johnson"
        },
        "LastClass": null
    }
]
```

By choosing the appropriate nesting mode (`nestMany`, `linkFirst`, or
`linkLast`), you can tailor your queries to efficiently handle nested data based
on your specific data structure and requirements.

> **Legacy old nest syntax**  
>
> There is a legacy nest syntax, that stills working underhood.
>
> ```php
> $groupsQuery->nest(['Students' => $studentsQuery], function (NestedSelect $nest, RowProxy $row) {
>     $nest->getSelect()->where('s.group_id', $row->group_id);
> }, NestMode::COLLECTION, Student::class);
> ```

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


Executing Stored Procedures [↑](#table-of-contents)
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
