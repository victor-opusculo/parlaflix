<?php

namespace VictorOpusculo\Parlaflix\Lib\Model\Students;

use DateTime;
use Exception;
use mysqli;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataProperty;
use VOpus\PhpOrm\Exceptions\DatabaseEntityNotFound;
use VOpus\PhpOrm\SqlSelector;

class Subscription extends DataEntity
{
    public function __construct(?array $initialValues = null)
    {
        $this->properties = (object)
        [
            'id' => new DataProperty(null, fn() => null, DataProperty::MYSQL_INT),
            'student_id' => new DataProperty(null, fn() => null, DataProperty::MYSQL_INT),
            'course_id' => new DataProperty(null, fn() => null, DataProperty::MYSQL_INT),
            'datetime' => new DataProperty(null, fn() => gmdate("Y-m-d H:i:s"), DataProperty::MYSQL_STRING)
        ];

        parent::__construct($initialValues);
    }

    protected string $databaseTable = 'student_subscriptions';
    protected string $formFieldPrefixName = 'student_subscriptions';
    protected array $primaryKeys = ['id'];

    public ?Course $course;

    public function getCountFromStudent(mysqli $conn, string $searchKeywords) : int
    {
        $selector = (new SqlSelector)
        ->addSelectColumn('COUNT(*)')
        ->setTable($this->databaseTable)
        ->addJoin("INNER JOIN courses ON courses.id = {$this->databaseTable}.course_id")
        ->addWhereClause("{$this->getWhereQueryColumnName('student_id')} = ?")
        ->addValue('i', $this->properties->student_id->getValue()->unwrapOr(0));

        if (mb_strlen($searchKeywords) > 3)
        {
            $selector = $selector
            ->addWhereClause(" AND MATCH (courses.name) AGAINST (?)")
            ->addValue('s', $searchKeywords);
        }

        return (int)$selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);
    }

    public function getMultipleFromStudent(mysqli $conn, string $searchKeywords, string $orderBy, int $page, int $numResultsOnPage) : array
    {
        $selector = $this->getGetSingleSqlSelector()
        ->clearValues()
        ->clearWhereClauses()
        ->addSelectColumn("COUNT(course_lessons.id) AS lessonCount")
        ->addSelectColumn("count(student_lesson_passwords.id) as doneLessonCount")
        ->addJoin("INNER JOIN courses ON courses.id = {$this->databaseTable}.course_id")
        ->addJoin("LEFT JOIN course_lessons ON course_lessons.course_id = {$this->databaseTable}.course_id")
        ->addJoin("LEFT JOIN student_lesson_passwords ON student_lesson_passwords.lesson_id = course_lessons.id")
        ->addWhereClause("{$this->getWhereQueryColumnName('student_id')} = ?")
        ->addValue('i', $this->properties->student_id->getValue()->unwrapOr(0));

        if (mb_strlen($searchKeywords) > 3)
        {
            $selector = $selector
            ->addWhereClause(" AND MATCH (courses.name) AGAINST (?)")
            ->addValue('s', $searchKeywords);
        }

        $selector = $selector->setOrderBy(match($orderBy)
        {
            'name' => "courses.name ASC",
            'datetime' => "{$this->databaseTable}.datetime DESC",
            default => "{$this->databaseTable}.datetime DESC"
        });

        $calcPage = ($page - 1) * $numResultsOnPage;
        $selector = $selector
        ->setLimit('?, ?')
        ->addValues('ii', [ $calcPage, $numResultsOnPage ])
        ->setGroupBy("{$this->databaseTable}.id");

        $drs = $selector->run($conn, SqlSelector::RETURN_ALL_ASSOC);
        return array_map([ $this, 'newInstanceFromDataRowFromDatabase' ], $drs);
    }

    public function getSingleFromStudent(mysqli $conn): static
    {
        $selector = $this->getGetSingleSqlSelector()
        ->addSelectColumn("COUNT(course_lessons.id) AS lessonCount")
        ->addSelectColumn("count(student_lesson_passwords.id) as doneLessonCount")
        ->addSelectColumn("SUM(course_lessons.completion_points) AS maxPoints")
        ->addSelectColumn("sum(if(student_lesson_passwords.is_correct = 1, course_lessons.completion_points, 0)) as studentPoints")
        ->addJoin("INNER JOIN courses ON courses.id = {$this->databaseTable}.course_id")
        ->addJoin("LEFT JOIN course_lessons ON course_lessons.course_id = {$this->databaseTable}.course_id")
        ->addJoin("LEFT JOIN student_lesson_passwords ON student_lesson_passwords.lesson_id = course_lessons.id")
        ->addWhereClause("AND {$this->getWhereQueryColumnName('student_id')} = ?")
        ->addValue('i', $this->properties->student_id->getValue()->unwrapOr(0));

        $dr = $selector->run($conn, SqlSelector::RETURN_SINGLE_ASSOC);
        if (isset($dr))
            return $this->newInstanceFromDataRowFromDatabase($dr);
        else
            throw new DatabaseEntityNotFound("Inscrição não encontrada!", $this->databaseTable);
    }

    public function getAllFromStudent(mysqli $conn) : array
    {
        $selector = $this->getGetSingleSqlSelector()
        ->clearValues()
        ->clearWhereClauses()
        ->addWhereClause($this->getWhereQueryColumnName('student_id') . ' = ?')
        ->addValue('i', $this->properties->student_id->getValue()->unwrapOr(0));

        $dr = $selector->run($conn, SqlSelector::RETURN_ALL_ASSOC);
        return array_map([$this, 'newInstanceFromDataRow'], $dr ?? []);
    }

    public function isStudentSubscribed(mysqli $conn) : bool
    {
        $selector = (new SqlSelector)
        ->addSelectColumn('COUNT(*)')
        ->setTable($this->databaseTable)
        ->addWhereClause("{$this->getWhereQueryColumnName('student_id')} = ?")
        ->addWhereClause(" AND {$this->getWhereQueryColumnName('course_id')} = ?")
        ->addValue('i', $this->properties->student_id->getValue()->unwrapOr(0))
        ->addValue('i', $this->properties->course_id->getValue()->unwrapOr(0));

        $count = $selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);
        return (int)$count > 0;
    }

    public function getSingleFromStudentAndCourse(mysqli $conn) : self
    {
        $selector = $this->getGetSingleSqlSelector()
        ->clearValues()
        ->clearWhereClauses()
        ->addWhereClause("{$this->getWhereQueryColumnName('student_id')} = ?")
        ->addWhereClause(" AND {$this->getWhereQueryColumnName('course_id')} = ?")
        ->addValue('i', $this->properties->student_id->getValue()->unwrapOr(0))
        ->addValue('i', $this->properties->course_id->getValue()->unwrapOr(0));

        $dr = $selector->run($conn, SqlSelector::RETURN_SINGLE_ASSOC);
        if (isset($dr))
            return $this->newInstanceFromDataRow($dr);
        else   
            throw new DatabaseEntityNotFound('Inscrição não localizada!', $this->databaseTable);
    }

    public function fetchCourse(mysqli $conn) : self
    {
        try
        {
            $this->course = (new Course([ 'id' => $this->properties->course_id->getValue()->unwrapOr(0) ]))->getSingle($conn);
        }
        catch (Exception $e) {}
        return $this;
    }
}