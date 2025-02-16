<?php
namespace VictorOpusculo\Parlaflix\Lib\Model\Courses;

use mysqli;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Student;
use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataProperty;
use VOpus\PhpOrm\Exceptions\DatabaseEntityNotFound;
use VOpus\PhpOrm\Option;
use VOpus\PhpOrm\SqlSelector;

/**
 * @property Option<int> id
 * @property Option<int> course_id
 * @property Option<int> student_id
 * @property Option<int> points
 * @property Option<string> message
 * @property Option<string> created_at
 */
final class Survey extends DataEntity
{
    public function __construct(?array $initialValues = null)
    {
        $this->properties = (object)
        [
            'id' => new DataProperty(null, fn() => null, DataProperty::MYSQL_INT),
            'course_id' => new DataProperty(null, fn() => null, DataProperty::MYSQL_INT),
            'student_id' => new DataProperty(null, fn() => null, DataProperty::MYSQL_INT),
            'points' => new DataProperty('points', fn() => null, DataProperty::MYSQL_INT),
            'message' => new DataProperty('hours', fn() => 0, DataProperty::MYSQL_STRING),
            'created_at' => new DataProperty('created_at', fn() => gmdate('Y-m-d H:i:s'), DataProperty::MYSQL_STRING)
        ];

        parent::__construct($initialValues);
    }

    protected string $databaseTable = 'course_surveys';
    protected string $formFieldPrefixName = 'course_surveys';
    protected array $primaryKeys = ['id'];

    public ?Course $course = null;
    public ?Student $student = null;

    public function existsFromStudentAndCourse(mysqli $conn) : bool
    {
        $selector = (new SqlSelector)
        ->addSelectColumn("COUNT(DISTINCT {$this->databaseTable}.id)")
        ->setTable($this->databaseTable)
        ->addWhereClause("{$this->databaseTable}.course_id = ?")
        ->addWhereClause("AND {$this->databaseTable}.student_id = ?")
        ->addValue('i', $this->course_id->unwrapOr(0))
        ->addValue('i', $this->student_id->unwrapOr(0));

        $count = (int)$selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);
        return $count > 0;
    }

    public function getSingle(mysqli $conn): static
    {
        $selector = $this->getGetSingleSqlSelector()
        ->addJoin("LEFT JOIN courses ON courses.id = {$this->databaseTable}.course_id")
        ->addJoin("LEFT JOIN students ON students.id = {$this->databaseTable}.student_id")
        ->addSelectColumn("courses.name AS courseName")
        ->addSelectColumn("AES_DECRYPT(students.full_name, '{$this->encryptionKey}') AS studentName");

        $dr = $selector->run($conn, SqlSelector::RETURN_SINGLE_ASSOC);

        if ($dr)
            return $this->newInstanceFromDataRowFromDatabase($dr);
        else
            throw new DatabaseEntityNotFound("Opinião não localizada!", $this->databaseTable);
    }

    public function getCount(mysqli $conn, string $searchKeywords) : int
    {
        $selector = (new SqlSelector)
        ->addSelectColumn("COUNT(DISTINCT {$this->databaseTable}.id)")
        ->setTable($this->databaseTable);

        if (mb_strlen($searchKeywords) > 3)
        {
            $selector = $selector
            ->addWhereClause("MATCH (message) AGAINST (?) ")
            ->addValue('s', $searchKeywords);
        }

        $count = (int)$selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);
        return $count;
    }

    public function getMultiple(mysqli $conn, string $searchKeywords, string $orderBy, int $page, int $numResultsOnPage) : array
    {
        $selector = $this->getGetSingleSqlSelector()
        ->addJoin("LEFT JOIN courses ON courses.id = {$this->databaseTable}.course_id")
        ->addJoin("LEFT JOIN students ON students.id = {$this->databaseTable}.student_id")
        ->addSelectColumn("courses.name AS courseName")
        ->addSelectColumn("AES_DECRYPT(students.full_name, '{$this->encryptionKey}') AS studentName")
        ->clearWhereClauses()
        ->clearValues();

        if (mb_strlen($searchKeywords) > 3)
        {
            $selector = $selector
            ->addWhereClause("MATCH (name) AGAINST (?) ")
            ->addValue('s', $searchKeywords);
        }

        $selector = $selector
        ->setOrderBy(match ($orderBy)
        {
            'points' => "{$this->databaseTable}.points ASC",
            'created_at' => "{$this->databaseTable}.created_at DESC",
            default => "{$this->databaseTable}.created_at DESC"
        });

        $calcPage = ($page - 1) * $numResultsOnPage;
        $selector = $selector
        ->setLimit('?,?')
        ->addValues('ii', [ $calcPage, $numResultsOnPage ])
        ->setGroupBy("{$this->databaseTable}.id");

        $drs = $selector->run($conn, SqlSelector::RETURN_ALL_ASSOC);
        return array_map([ $this, 'newInstanceFromDataRow' ], $drs);
    }

    public function fetchCourse(mysqli $conn) : self
    {
        $this->course = (new Course([ 'id' => $this->course_id->unwrapOr(0) ]))->getSingle($conn);
        return $this;
    }

    public function fetchStudent(mysqli $conn) : self
    {
        $getter = new Student([ 'id' => $this->student_id->unwrapOr(0) ]);
        $getter->setCryptKey($this->encryptionKey);
        $this->student = $getter->getSingle($conn);
        return $this;
    }
}