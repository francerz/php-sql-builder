<?php

namespace Francerz\SqlBuilder\Tests;

use Francerz\SqlBuilder\Components\Nest;
use Francerz\SqlBuilder\Driver\QueryCompiler;
use Francerz\SqlBuilder\Nesting\NestedSelect;
use Francerz\SqlBuilder\Nesting\NestMerger;
use Francerz\SqlBuilder\Nesting\NestTranslator;
use Francerz\SqlBuilder\Nesting\RowProxy;
use Francerz\SqlBuilder\Query;
use Francerz\SqlBuilder\Results\SelectResult;
use Francerz\SqlBuilder\SelectQuery;
use PHPUnit\Framework\TestCase;
use stdClass;

class SelectNestableTest extends TestCase
{
    public function getGroupsQuery()
    {
        /*
            SQL:
            SELECT g.group_id, g.subject, g.teacher FROM groups AS g WHERE g.group_id IN (3, 7 , 11)
        */
        $query = Query::selectFrom(['g' => 'groups'], ['group_id', 'subject', 'teacher']);
        $query->where()->in('g.group_id', [3, 7, 11]);

        /*
            SQL:
            SELECT s.*, gs.group_id
            FROM students AS s
            INNER JOIN groups_students AS gs ON s.student_id = gs.student_id
            WHERE gs.group_id IN (3, 7, 11)
        */
        $studentsQuery = Query::selectFrom(['s' => 'students']);
        $query->nest(['Students' => $studentsQuery], function (NestedSelect $nest, RowProxy $row) {
            // common structure for nesting,
            $nest->getSelect()
                ->innerJoin(['gs' => 'groups_students'], ['group_id'])
                ->on()->equals('s.student_id', 'gs.student_id');

            // result varies per $row values.
            $nest->getSelect()
                ->where()->equals('gs.group_id', $row->group_id);
        });

        return $query;
    }

    public function getGroupsExpectedResult()
    {
        $grupos = array(
            ["group_id" => 3],
            ["group_id" => 7],
            ["group_id" => 11]
        );
        return new SelectResult(json_decode(json_encode($grupos)));
    }

    public function getStudentsExpectedResult()
    {
        $students = array(
            ['group_id' => 3, 'student_id' => 13, 'name' => 'John Doe'],
            ['group_id' => 11, 'student_id' => 17, 'name' => 'Janne Doe'],
            ['group_id' => 7, 'student_id' => 19, 'name' => 'James Doe'],
            ['group_id' => 7, 'student_id' => 23, 'name' => 'Judy Doe']
        );
        return new SelectResult(json_decode(json_encode($students)));
    }

    public function getGroupsNestedResult()
    {
        $groups = array(
            ["group_id" => 3, 'Students' => [
                ['group_id' => 3, 'student_id' => 13, 'name' => 'John Doe']
            ]],
            ["group_id" => 7, 'Students' => [
                ['group_id' => 7, 'student_id' => 19, 'name' => 'James Doe'],
                ['group_id' => 7, 'student_id' => 23, 'name' => 'Judy Doe']
            ]],
            ["group_id" => 11, 'Students' => [
                ['group_id' => 11, 'student_id' => 17, 'name' => 'Janne Doe']
            ]]
        );
        return new SelectResult(json_decode(json_encode($groups)));
    }

    public function testNestable()
    {
        $nestTranslator = new NestTranslator();
        $compiler = new QueryCompiler();
        $merger = new NestMerger();

        $query = $this->getGroupsQuery();
        $groups = $this->getGroupsExpectedResult($query);

        $nests = $query->getNests();
        $this->assertEquals(1, count($nests));

        $nest = $nests[0];
        if (!$nest instanceof Nest) {
            return;
        }

        $nested = $nest->getNested();
        $nestSelect = $nested->getSelect();
        $nestTranslate = $nestTranslator->translate($nestSelect, $groups);

        $nestCompiled = $compiler->compileSelect($nestTranslate);
        $expected = 'SELECT s.*, gs.group_id FROM students AS s INNER JOIN groups_students AS gs ON s.student_id = gs.student_id WHERE gs.group_id IN (:v1, :v2, :v3)';
        $this->assertEquals($expected, $nestCompiled->getQuery());
        $this->assertEquals(['v1' => 3, 'v2' => 7, 'v3' => 11], $nestCompiled->getValues());

        $students = $this->getStudentsExpectedResult($nestTranslate);

        $merger->merge($groups, $students, $nest);
        $groupsNested = $this->getGroupsNestedResult($query);

        $this->assertEquals($groupsNested, $groups);
    }

    public function getNewGroupsQuery()
    {
        $query = Query::selectFrom('groups AS g', ['group_id', 'subject', 'teacher']);
        $query->where()->in('g.group_id', [3, 7, 11]);

        $studentsQuery = Query::selectFrom('students AS s');
        $studentsQuery->innerJoin('groups_students AS gs', ['group_id'])
            ->on('s.student_id', 'gs.student_id');
        $query->nestMany('Students', $studentsQuery, $row, stdClass::class)
            ->where('gs.group_id', $row->group_id);
        return $query;
    }

    public function testNestMany()
    {
        $nestTranslator = new NestTranslator();
        $compiler = new QueryCompiler();
        $merger = new NestMerger();

        $query = $this->getNewGroupsQuery();
        $groups = $this->getGroupsExpectedResult();
        $nests = $query->getNests();
        $this->assertCount(1, $nests);

        $nest = $nests[0];
        if (!$nest instanceof Nest) {
            return;
        }

        $nested = $nest->getNested();
        $nestSelect = $nested->getSelect();
        $nestTranslate = $nestTranslator->translate($nestSelect, $groups);

        $nestCompiled = $compiler->compileSelect($nestTranslate);
        $expected = 'SELECT s.*, gs.group_id FROM students AS s INNER JOIN groups_students AS gs ON s.student_id = gs.student_id WHERE gs.group_id IN (:v1, :v2, :v3)';
        $this->assertEquals($expected, $nestCompiled->getQuery());
        $this->assertEquals(['v1' => 3, 'v2' => 7, 'v3' => 11], $nestCompiled->getValues());

        $students = $this->getStudentsExpectedResult($nestTranslate);

        $merger->merge($groups, $students, $nest);
        $groupsNested = $this->getGroupsNestedResult($query);

        $this->assertEquals($groupsNested, $groups);

    }
}
