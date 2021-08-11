<?php

use Francerz\SqlBuilder\Driver\QueryCompiler;
use Francerz\SqlBuilder\InsertQuery;
use Francerz\SqlBuilder\Query;
use PHPUnit\Framework\TestCase;

class InsertQueryTest extends TestCase
{
    private $compiler;

    public function __construct()
    {
        parent::__construct();
        $this->compiler = new QueryCompiler();
    }
    public function testInsertQueryAssoc()
    {
        $query = Query::insertInto('grupos', ['periodo_id'=>80,'materia_id'=>2312]);

        $this->assertInstanceOf(InsertQuery::class, $query);
        $this->assertEquals(1, count($query));

        $compiled = $this->compiler->compileQuery($query);

        $this->assertEquals('INSERT INTO grupos(periodo_id,materia_id) VALUES (:v1,:v2)', $compiled->getQuery());
        $this->assertEquals(['v1'=>80,'v2'=>2312], $compiled->getValues());
    }

    public function testInsertQueryObject()
    {
        $grupo = new stdClass();
        $grupo->periodo_id = 80;
        $grupo->materia_id = 2312;

        $query = Query::insertInto('grupos', $grupo);

        $this->assertInstanceOf(InsertQuery::class, $query);
        $this->assertEquals(1, count($query));

        $compiled = $this->compiler->compileQuery($query);

        $this->assertEquals('INSERT INTO grupos(periodo_id,materia_id) VALUES (:v1,:v2)', $compiled->getQuery());
        $this->assertEquals(['v1'=>80,'v2'=>2312], $compiled->getValues());
    }

    public function testInsertQueryArrayObject()
    {
        $grupos[] = $grupo = new stdClass();
        $grupo->periodo_id = 80;
        $grupo->materia_id = 2312;
        $grupos[] = $grupo = new stdClass();
        $grupo->periodo_id = 30;
        $grupo->materia_id = 647;
        $grupo->base_grupo_id = 2;

        $query = Query::insertInto('grupos', $grupos);

        $this->assertInstanceOf(InsertQuery::class, $query);
        $this->assertEquals(2, count($query));
        $this->assertEquals(['periodo_id','materia_id','base_grupo_id'], $query->getColumns());
        
        $compiled = $this->compiler->compileQuery($query);

        $this->assertEquals(
            'INSERT INTO grupos(periodo_id,materia_id,base_grupo_id) VALUES '.
            '(:v1,:v2,NULL),(:v3,:v4,:v5)',
            $compiled->getQuery()
        );
        $this->assertEquals(['v1'=>80,'v2'=>2312,'v3'=>30,'v4'=>647,'v5'=>2], $compiled->getValues());
    }

    public function testInsertQuerySelectedColumns()
    {
        $grupo = new stdClass();
        $grupo->periodo_id = 80;
        $grupo->materia_id = 3213;
        $grupo->base_grupo_id = 3;

        $query = Query::insertInto('grupos', $grupo, ['periodo_id','materia_id','empleado_id']);
        
        $this->assertInstanceOf(InsertQuery::class, $query);
        $this->assertEquals(['periodo_id','materia_id'], $query->getColumns());

        $query = Query::insertInto('grupos', (array)$grupo, ['periodo_id','materia_id','empleado_id']);
        $this->assertEquals(['periodo_id','materia_id'], $query->getColumns());

        $compiled = $this->compiler->compileQuery($query);

        $this->assertEquals(
            'INSERT INTO grupos(periodo_id,materia_id) VALUES (:v1,:v2)',
            $compiled->getQuery()
        );
        $this->assertEquals(['v1'=>80,'v2'=>3213], $compiled->getValues());
    }
}