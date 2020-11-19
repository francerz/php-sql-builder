SQL Builder
=======================================

Installation
---------------------------------------

```bash
composer require francerz/sql-builder
```

## Creating `SELECT` query

```php
$query = Query::selectFrom(['g'=>'groups'],['group_id','subject','teacher']);
```

Compiles to:

```sql
SELECT g.group_id, g.subject, g.teacher FROM groups AS g
```

### Adding `WHERE` clause

You can use the `WHERE` clause by retreiving the `ConditionsList` object
returned by calling the `where()` method.

```php
$query = Query::selectFrom(['g'=>'groups'],['group_id','subject','teacher']);
$query->where()->like('g.subject','%programming%');
```

Outputs:

```sql
SELECT g.group_id, g.subject, g.teacher FROM groups AS g WHERE g.subject LIKE '%programming%'
```

`ConditionsList` contains multiple methods each operator:

- **Relative operators:** `equals()`, `notEquals()`, `lessThan()`, `lessEquals()`, `greaterThan()`, `greaterEquals()`.
  
  > Also there are prefixed logic connectors (`and`, `or`) methods like: `andLessThan()`, `orEquals()`.
- **LIKE operator:** `like()`, `notLike()`, `andLike()`, `orLike()`, `andNotLike()` and `orNotLike()`.
- **IS NULL operator:** `null()`, `notNull()`, `andNull()`, `orNull()`, `andNotNull()` and `orNotNull()`.
- **BETWEEN operator:** `between()`, `notBetween()`, `andBetween()`, `orBetween()`, `andNotBetween()` and `orNotBetween()`.
- **IN operator:** `in()`, `notIn()`, `andIn()`, `orIn()`, `andNotIn()` and `orNotIn()`.

#### Parentheses expressions

Conditions list allows using parentheses expresions by using a nested condition
inside a callback.

To pass the callback you must use the `__invoke()`, `not()`, `and()`, `or()`, `andNot()` and `orNot()`. 

```php
$query->where()(function($subwhere) {
    $subwhere
        ->between('g.group_id', 1, 100)
        ->orNull('g.teacher');
});
```

Output:

```sql
... WHERE (g.group_id BETWEEN 1 AND 100 OR g.teacher IS NULL)
```

#### Equals or NULL function

Theres also a method called `equalsOrNull()`, wich makes a perenthesis comparation
between a value and another, or check if is null.

```php
$query->where()->equalsOrNull('g.subject','Database Fundamentals');
```

Outputs:
```sql
... WHERE (g.subject = 'Database Fundamentals' OR g.subject IS NULL)
```

> **NOTE:**  
> The `ConditionsList` object is also available with `JOINS `***`ON`***
> and `HAVING` clause.

### Joining Tables

One of the most common action with databases is joining data from multiple tables.
This package support most common joining operations. And each driver might translate
these operations to compatible SQL Queries, meaning full support on any database
engine.

Joining functions are: `crossJoin()`, `innerJoin()`, `leftJoin()`, `rightJoin()`, `leftOuterJoin()`, `rightOuterJoin()` and `fullOuterJoin()`.

```php
$query = Query::selectFrom(['g'=>'groups'], ['group_id']);
$query->innerJoin(['s'=>'subjects'], ['subject'=>'name'])
    ->on()->equals('s.subject_id', 'g.subject_id');
$query->leftJoin(['t'=>'teachers'], ['teacher'=>"CONCAT(first_name,' ',last_name)"])
    ->on()->equals('t.teacher_id', 'g.teacher_id');
$query->where()->in('g.group_id', [3, 5, 7, 11]);
```

Translates to:
```sql
SELECT g.group_id, s.name AS subject, CONCAT(first_name,' ',last_name) AS teacher
FROM groups AS g
INNER JOIN subjects AS s ON s.subject_id = g.subject_id
LEFT JOIN teachers AS t ON t.teacher_id = g.teacher_id
WHERE g.group_id IN (3, 5, 7, 11);
```

> **NOTE:**
> Join Syntax is available to `SELECT`, `UPDATE` and `DELETE` sql syntax,
> however, not all database engines might support it.

### SELECT nesting

Sometimes database table joining might not be enought for all the data requirements.
Is quite often that for each row in a result of a query, another filtered result
query must be executed.

This scenario produces excesive complex code to nest each result by each row.
Also impacts performance by increasing the loops and database access roundtrips.
For this reason there's a syntax that creates the most lightweight and efficient
way to query nested data, preventing access overhead and reducing processing time.

```php
// Students query
$studentsQuery = Query::selectFrom('students', ['student_id','first_name', 'last_name']);

// Groups query
$groupsQuery = Query::selectFrom('groups',['group_id','subject','teacher']);

// Nesting students by each group
$groupsQuery->nest(['Students'=>$studentsQuery], function(NestedSelect $nest, RowProxy $row) {
    $nest->getSelect()->where()->equals('s.group_id', $row->group_id);
});
```

Result might be like this:
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