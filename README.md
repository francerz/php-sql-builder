SQL Builder
---------------------------------------

```php
$query = Query::selectFrom(['t1'=>'table1'],['column1','column2','column3']);
$query->innerJoin(['t2'=>'table2'],['column1'])->on();
$query->where('t1.column1', 2);
$query->groupBy('');
$query->having('');
$query->orderBy('');
```
```sql
SELECT `t1`.`column1`, `t1`.`column2`, `t1`.`column3`, `t2`.`column1`
FROM `table1` AS `t1`
INNER JOIN `table2` AS `t2`
WHERE `t1.column1` = 2;
```




### Comparison expressions

The results of theses expressions returns a `TRUE` or `FALSE` value.

```sql
-- Relational expression
operand1 = operand2
operand1 < operand2
operand1 > operand2
operand1 <= operand2
operand1 >= operand2
operand1 <> operand2

-- String compare expression
operand1 LIKE operand2
operand1 NOT LIKE operand2

operand1 REGEXP operand2
operand1 NOT REGEXP operand2

-- NULL expression
operand1 IS NULL
operand1 IS NOT NULL

-- BETWEEN expression
operand1 BETWEEN operand2 AND operand3
operand1 NOT BETWEEN operand2 AND operand3

-- SET expression
operand1 IN (VALUES SET...)
operand1 NOT IN (VALUES SET...)
```

### Logical expressions

```sql
operand1 AND operand2

operand1 OR operand2

operand1 XOR operand2

NOT operand1
```