<?php

namespace Francerz\SqlBuilder\Tests;

use Francerz\SqlBuilder\Components\Column;
use Francerz\SqlBuilder\Components\Table;
use Francerz\SqlBuilder\Driver\QueryCompiler;
use Francerz\SqlBuilder\Expressions\Logical\ConditionList;
use Francerz\SqlBuilder\Query;
use Francerz\SqlBuilder\SelectQuery;
use PHPUnit\Framework\TestCase;

class SelectQueryTest extends TestCase
{

    private $compiler;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->compiler = new QueryCompiler();
    }
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

        $query = Query::selectFrom(new Table('grupos', 'g', 'siitecdb'));
        $query
            ->innerJoin(['a' => 'asignaturas'])
            ->on()->equals('a.id_asignatura', 'g.id_asignatura');
        $query
            ->innerJoin(['p' => 'periodos'])
            ->on()->equals('p.id_periodo', 'g.id_periodo');
        $query
            ->leftJoin(['pe' => 'planes_estudios'])
            ->on()->equals('pe.id_plan_estudio', 'g.id_plan_estudio');

        $query->where()
            ->between(Query::value(date('Y-m-d')), Query::column('p.inicio'), Query::column('p.fin'))
            ->and(function (ConditionList $where) use ($id_carrera) {
                $where
                    ->equals('pe.id_carrera', $id_carrera)
                    ->orNull('pe.id_plan_estudio');
            })
            ->equalsOrNull('pe.id_carrera', $id_carrera);
        $query->orderBy('g.id_grupo');

        $query->columns([
            'nombre' => 'COALESCE(g.nombre, a.nombre)',
            'other' => Query::func('IF', Query::cond()->null('g.base_grupo_id'), 0, 1)
        ]);

        $expected = "SELECT
            COALESCE(g.nombre, a.nombre) AS nombre,
            IF((g.base_grupo_id IS NULL), :v1, :v2) AS other,
            g.*
        FROM siitecdb.grupos AS g
        INNER JOIN asignaturas AS a
            ON a.id_asignatura = g.id_asignatura
        INNER JOIN periodos AS p
            ON p.id_periodo = g.id_periodo
        LEFT JOIN planes_estudios AS pe
            ON pe.id_plan_estudio = g.id_plan_estudio
        WHERE
            :v3 BETWEEN p.inicio AND p.fin
            AND (pe.id_carrera = :v4
                OR pe.id_plan_estudio IS NULL)
            AND (pe.id_carrera = :v5 OR pe.id_carrera IS NULL)
        ORDER BY g.id_grupo ASC";

        $compiled = $this->compiler->compileSelect($query);

        $this->assertEquals([
            'v1' => 0,
            'v2' => 1,
            'v3' => date('Y-m-d'),
            'v4' => $id_carrera,
            'v5' => $id_carrera
        ], $compiled->getValues());
        $this->assertEquals(preg_replace('/\s+/', ' ', $expected), $compiled->getQuery());
    }

    public function testCompilingTest()
    {
        $query = Query::selectFrom(['t' => 'table']);
        $compiled = $this->compiler->compileSelect($query);

        $this->assertEquals('SELECT t.* FROM table AS t', $compiled->getQuery());
        $this->assertEquals([], $compiled->getValues());

        // --------

        $query->crossJoin(['t2' => 'table2']);
        $compiled = $this->compiler->compileSelect($query);

        $this->assertEquals('SELECT t.* FROM table AS t, table2 AS t2', $compiled->getQuery());
        $this->assertEquals([], $compiled->getValues());

        // --------

        $subquery = Query::selectFrom('table_a AS a');
        $query->innerJoin(['t3' => $subquery])->on()
            ->equals('t3.col', 't2.col')
            ->andLessEquals('t3.col2', 't.c');
        $compiled = $this->compiler->compileSelect($query);

        $this->assertEquals(
            'SELECT t.* FROM table AS t, table2 AS t2 ' .
                'INNER JOIN (SELECT a.* FROM table_a AS a) AS t3 ' .
                'ON t3.col = t2.col AND t3.col2 <= t.c',
            $compiled->getQuery()
        );
        $this->assertEquals([], $compiled->getValues());

        // --------

        $query->where()->notBetween('t.alpha', 16, 80);
        $compiled = $this->compiler->compileSelect($query);

        $expected = "SELECT t.* FROM table AS t, table2 AS t2
            INNER JOIN (SELECT a.* FROM table_a AS a) AS t3
            ON t3.col = t2.col AND t3.col2 <= t.c
            WHERE t.alpha NOT BETWEEN :v1 AND :v2";

        $this->assertEquals(preg_replace('/\s+/', ' ', $expected), $compiled->getQuery());
        $this->assertEquals(['v1' => 16, 'v2' => 80], $compiled->getValues());
    }

    public function testWhereArgs()
    {
        $query = Query::selectFrom('groups');

        $query->where(function (ConditionList $where) {
            $where->in('group_id', [3, 5, 7, 11]);
        });
        $query->where('a', 'b')
            ->and('c', 'NULL')
            ->or('d', 'BETWEEN', 'e', 'f');

        $compiled = $this->compiler->compileSelect($query);

        $expected = "SELECT groups.* FROM groups WHERE (group_id IN (:v1, :v2, :v3, :v4)) " .
            "AND a = :v5 AND c IS NULL OR d BETWEEN :v6 AND :v7";

        $this->assertEquals($expected, $compiled->getQuery());
    }

    public function testWhereNot()
    {
        $query = Query::selectFrom(['groups']);
        $query->where()->not('group_id', 1);

        $compiled = $this->compiler->compileSelect($query);

        $expected = "SELECT groups.* FROM groups WHERE NOT group_id = :v1";
        $this->assertEquals($expected, $compiled->getQuery());
    }
}
