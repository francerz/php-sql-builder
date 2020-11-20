<?php

use Francerz\SqlBuilder\GenericCompiler;
use Francerz\SqlBuilder\Components\Column;
use Francerz\SqlBuilder\Components\Table;
use Francerz\SqlBuilder\Driver\QueryCompiler;
use Francerz\SqlBuilder\Expressions\Logical\ConditionList;
use Francerz\SqlBuilder\Query;
use Francerz\SqlBuilder\SelectQuery;
use PHPUnit\Framework\TestCase;

class SelectQueryTest extends TestCase
{
    public function testCreateSelectQuery()
    {
        $query = new SelectQuery('table1', ['column1', 'column2']);

        $cols = Column::fromArray(array(
            'table1.column1',
            'table1.column2'
        ));
        $this->assertEquals('table1', $query->getFrom()->getTable()->getSource());
        $this->assertEquals($cols, $query->getFrom()->getColumns());

        return $query;
    }
    public function testJoins()
    {
        $id_carrera = 6;

        $query = Query::selectFrom(new Table('grupos','g','siitecdb'));
        $query
            ->innerJoin(['a'=>'asignaturas'])
            ->on()->equals('a.id_asignatura', 'g.id_asignatura');
        $query
            ->innerJoin(['p'=>'periodos'])
            ->on()->equals('p.id_periodo', 'g.id_periodo');
        $query
            ->leftJoin(['pe'=>'planes_estudios'])
            ->on()->equals('pe.id_plan_estudio', 'g.id_plan_estudio');
        
        $query->where()
            ->between(Query::value(date('Y-m-d')), Query::column('p.inicio'), Query::column('p.fin'))
            ->and(function(ConditionList $where) use ($id_carrera) {
                $where
                    ->equals('pe.id_carrera', $id_carrera)
                    ->orNull('pe.id_plan_estudio');
            })
            ->equalsOrNull('pe.id_carrera', $id_carrera);
        
        $expected = "SELECT g.*
        FROM siitecdb.grupos AS g
        INNER JOIN asignaturas AS a
            ON a.id_asignatura = g.id_asignatura
        INNER JOIN periodos AS p
            ON p.id_periodo = g.id_periodo
        LEFT JOIN planes_estudios AS pe
            ON pe.id_plan_estudio = g.id_plan_estudio
        WHERE
            :v1 BETWEEN p.inicio AND p.fin
            AND (pe.id_carrera = :v2
                OR pe.id_plan_estudio IS NULL)
            AND (pe.id_carrera = :v3 OR pe.id_carrera IS NULL)";

        $compiler = new QueryCompiler();
        $compiled = $compiler->compileQuery($query);
        
        $this->assertEquals(preg_replace('/\s+/',' ', $expected), $compiled->getQuery());
        $this->assertEquals(['v1'=>date('Y-m-d'),'v2'=>$id_carrera,'v3'=>$id_carrera], $compiled->getValues());
    }

    public function testCompilingTest()
    {
        $compiler = new QueryCompiler();

        $query = Query::selectFrom(['t'=>'table']);
        $compiled = $compiler->compileQuery($query);

        $this->assertEquals('SELECT t.* FROM table AS t', $compiled->getQuery());
        $this->assertEquals([], $compiled->getValues());

        // --------

        $query->crossJoin(['t2'=>'table2']);
        $compiled = $compiler->compileQuery($query);

        $this->assertEquals('SELECT t.* FROM table AS t, table2 AS t2', $compiled->getQuery());
        $this->assertEquals([], $compiled->getValues());

        // --------

        $subquery = Query::selectFrom('table_a AS a');
        $query->innerJoin(['t3'=>$subquery])->on()
            ->equals('t3.col','t2.col')
            ->andLessEquals('t3.col2','t.c');
        $compiled = $compiler->compileQuery($query);

        $this->assertEquals(
            'SELECT t.* FROM table AS t, table2 AS t2 '.
            'INNER JOIN (SELECT a.* FROM table_a AS a) AS t3 '.
            'ON t3.col = t2.col AND t3.col2 <= t.c',
            $compiled->getQuery()
        );
        $this->assertEquals([], $compiled->getValues());

        // --------

        $query->where()->notBetween('t.alpha', 16, 80);
        $compiled = $compiler->compileQuery($query);

        $expected = "SELECT t.* FROM table AS t, table2 AS t2 
            INNER JOIN (SELECT a.* FROM table_a AS a) AS t3 
            ON t3.col = t2.col AND t3.col2 <= t.c 
            WHERE t.alpha NOT BETWEEN :v1 AND :v2";

        $this->assertEquals(preg_replace('/\s+/', ' ', $expected), $compiled->getQuery());
        $this->assertEquals(['v1'=>16,'v2'=>80], $compiled->getValues());
    }
}