<?php

use Francerz\SqlBuilder\Query;
use Francerz\SqlBuilder\SelectQuery;
use PHPUnit\Framework\TestCase;

class SelectNestableTest extends TestCase
{
    public function testNestable()
    {
        $queryEstudiantes = Query::selectFrom(['e'=>'estudiantes']);
        $queryEstudiantes
            ->innerJoin(['ge'=>'grupos_estudiantes'], ['id_grupo'])
            ->on()->equals('e.id_estudiante', 'ge.id_estudiante');

        $query = Query::selectFrom(['g'=>'grupos']);

        $query->nest(['Estudiantes'=>$queryEstudiantes], function (SelectQuery $query, $row) {
            $query->where()->equals('id_grupo', $row->id_grupo);
        });

        $this->assertEquals(1, count($query->getNests()));
    }
}