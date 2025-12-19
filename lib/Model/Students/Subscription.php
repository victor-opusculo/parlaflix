<?php

namespace VictorOpusculo\Parlaflix\Lib\Model\Students;

use DateTime;
use Exception;
use mysqli;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\PresenceMethod;
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

    public private(set) ?Course $course;
    public private(set) ?Student $student;

    public function idBelongsToStudent(mysqli $conn) : bool
    {
        $selector = new SqlSelector()
        ->setTable($this->databaseTable)
        ->addSelectColumn("COUNT({$this->getSelectQueryColumnName('id')})")
        ->addWhereClause("{$this->getWhereQueryColumnName('id')} = ?")
        ->addWhereClause(" AND {$this->getWhereQueryColumnName('student_id')} = ?")
        ->addValues("ii", [ $this->id->unwrapOr(0), $this->student_id->unwrapOr(0) ]);

        $count = (int)$selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);
        return $count > 0;
    }

    public function getSingleWithProgressData(mysqli $conn) : self
    {
        $selector = $this->getGetSingleSqlSelector()
        ->addSelectColumn("AES_DECRYPT(students.full_name, '{$this->encryptionKey}') AS studentName")
        ->addSelectColumn("COUNT(course_lessons.id) AS lessonCount")
        ->addSelectColumn("count(lessons_results.clid) as doneLessonCount")
        ->addSelectColumn("SUM(course_lessons.completion_points) AS maxPoints")
        ->addSelectColumn("sum(lessons_results.completion_points) as studentPoints")
        ->addJoin("INNER JOIN courses ON courses.id = {$this->databaseTable}.course_id")
        ->addJoin("INNER JOIN students ON students.id = {$this->databaseTable}.student_id")
        ->addJoin("LEFT JOIN course_lessons ON course_lessons.course_id = {$this->databaseTable}.course_id")
        ->addJoin(self::getProgressDataJoinTableSql());

        $dr = $selector->run($conn, SqlSelector::RETURN_SINGLE_ASSOC);

        if (isset($dr))
            return $this->newInstanceFromDataRow($dr);
        else
            throw new DatabaseEntityNotFound("Inscrição não encontrada!", $this->databaseTable);
    }

    public function getCount(mysqli $conn, string $searchKeywords) : int
    {
        $selector = (new SqlSelector)
        ->addSelectColumn('COUNT(*)')
        ->setTable($this->databaseTable)
        ->addJoin("INNER JOIN courses ON courses.id = {$this->databaseTable}.course_id")
        ->addJoin("INNER JOIN students ON students.id = {$this->databaseTable}.student_id");

        if (mb_strlen($searchKeywords) > 3)
        {
            $selector = $selector
            ->addWhereClause("MATCH (courses.name) AGAINST (?)")
            ->addWhereClause("OR Convert(AES_DECRYPT(students.full_name, '{$this->encryptionKey}') using 'utf8mb4') LIKE ?")
            ->addValues('ss', [ "%$searchKeywords%", "%$searchKeywords%" ]);
        }

        return (int)$selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);
    }

    public function getMultiple(mysqli $conn, string $searchKeywords, string $orderBy, int $page, int $numResultsOnPage) : array
    {
        $selector = $this->getGetSingleSqlSelector()
        ->clearValues()
        ->clearWhereClauses()
        ->addSelectColumn("AES_DECRYPT(students.full_name, '{$this->encryptionKey}') AS studentName")
        ->addSelectColumn("COUNT(course_lessons.id) AS lessonCount")
        ->addSelectColumn("count(lessons_results.clid) as doneLessonCount")
        ->addSelectColumn("SUM(course_lessons.completion_points) AS maxPoints")
        ->addSelectColumn("sum(lessons_results.completion_points) as studentPoints")
        ->addJoin("INNER JOIN courses ON courses.id = {$this->databaseTable}.course_id")
        ->addJoin("INNER JOIN students ON students.id = {$this->databaseTable}.student_id")
        ->addJoin("LEFT JOIN course_lessons ON course_lessons.course_id = {$this->databaseTable}.course_id")
        ->addJoin(self::getProgressDataJoinTableSql());

        if (mb_strlen($searchKeywords) > 3)
        {
            $selector = $selector
            ->addWhereClause("MATCH (courses.name) AGAINST (?)")
            ->addWhereClause("OR Convert(AES_DECRYPT(students.full_name, '{$this->encryptionKey}') using 'utf8mb4') LIKE ?")
            ->addValues('ss', [ "%$searchKeywords%", "%$searchKeywords%" ]);
        }

        $selector = $selector->setOrderBy(match($orderBy)
        {
            'name' => "courses.name ASC",
            'datetime' => "{$this->databaseTable}.datetime DESC",
            'id' => "{$this->databaseTable}.id DESC",
            default => "{$this->databaseTable}.id DESC"
        });

        $calcPage = ($page - 1) * $numResultsOnPage;
        $selector = $selector
        ->setLimit('?, ?')
        ->addValues('ii', [ $calcPage, $numResultsOnPage ])
        ->setGroupBy("{$this->databaseTable}.id");

        $drs = $selector->run($conn, SqlSelector::RETURN_ALL_ASSOC);
        return array_map([ $this, 'newInstanceFromDataRowFromDatabase' ], $drs);
    }

    public function getCountFromStudent(mysqli $conn, string $searchKeywords, ?int $categoryId = null, ?bool $includeOnlyMembers = false) : int
    {
        $selector = (new SqlSelector)
        ->addSelectColumn('COUNT(*)')
        ->setTable($this->databaseTable)
        ->addJoin("INNER JOIN courses ON courses.id = {$this->databaseTable}.course_id")
        ->addJoin("LEFT JOIN courses_categories_join ON courses_categories_join.course_id = {$this->databaseTable}.course_id")
        ->addWhereClause("{$this->getWhereQueryColumnName('student_id')} = ?")
        ->addValue('i', $this->properties->student_id->getValue()->unwrapOr(0));

        if (mb_strlen($searchKeywords) > 3)
        {
            $selector = $selector
            ->addWhereClause(" AND MATCH (courses.name) AGAINST (?)")
            ->addValue('s', $searchKeywords);
        }

        if (!$includeOnlyMembers)
        {
            $selector = $selector
            ->addWhereClause(" AND courses.members_only = 0");
        }

        if ($categoryId)
        {
            $selector = $selector
            ->addWhereClause(" AND courses_categories_join.category_id = ?")
            ->addValue('i', $categoryId);
        }

        return (int)$selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);
    }

    public function getMultipleFromStudent(mysqli $conn, string $searchKeywords, string $orderBy, int $page, int $numResultsOnPage, ?int $categoryId = null, ?bool $includeOnlyMembers = false) : array
    {
        print_r($this->id->unwrapOr(0));

        $selector = $this->getGetSingleSqlSelector()
        ->clearValues()
        ->clearWhereClauses()
        ->addSelectColumn("COUNT(course_lessons.id) AS lessonCount")
        ->addSelectColumn("count(lessons_results.clid) as doneLessonCount")
        ->addJoin("INNER JOIN courses ON courses.id = {$this->databaseTable}.course_id")
        ->addJoin("LEFT JOIN course_lessons ON course_lessons.course_id = {$this->databaseTable}.course_id")
        ->addJoin("LEFT JOIN courses_categories_join ON courses_categories_join.course_id = {$this->databaseTable}.course_id")
        ->addJoin(self::getProgressDataJoinTableSql($this->id->unwrapOr(0)))
        ->addWhereClause("{$this->getWhereQueryColumnName('student_id')} = ?")
        ->addValue('i', $this->properties->student_id->getValue()->unwrapOr(0));

        if (mb_strlen($searchKeywords) > 3)
        {
            $selector = $selector
            ->addWhereClause(" AND MATCH (courses.name) AGAINST (?)")
            ->addValue('s', $searchKeywords);
        }

        if (!$includeOnlyMembers)
        {
            $selector = $selector
            ->addWhereClause(" AND courses.members_only = 0");
        }

        if ($categoryId)
        {
            $selector = $selector
            ->addWhereClause(" AND courses_categories_join.category_id = ?")
            ->addValue('i', $categoryId);
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

    public function getCountFromCourse(mysqli $conn, string $searchKeywords) : int
    {
        $selector = (new SqlSelector)
        ->addSelectColumn('COUNT(*)')
        ->setTable($this->databaseTable)
        ->addJoin("INNER JOIN students ON students.id = {$this->databaseTable}.student_id")
        ->addWhereClause("{$this->getWhereQueryColumnName('course_id')} = ?")
        ->addValue('i', $this->properties->course_id->getValue()->unwrapOr(0));

        if (mb_strlen($searchKeywords) > 3)
        {
            $selector = $selector
            ->addWhereClause(" AND (Convert(AES_DECRYPT(students.full_name, '{$this->encryptionKey}') using 'utf8mb4') like ?
            OR Convert(AES_DECRYPT(students.email, '{$this->encryptionKey}') using 'utf8mb4') like ? )")
            ->addValues('ss', [ "%$searchKeywords%", "%$searchKeywords%" ]);
        }

        return (int)$selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);
    }

    public function getMultipleFromCourse(mysqli $conn, string $searchKeywords, string $orderBy, ?int $page, ?int $numResultsOnPage) : array
    {
        $selector = $this->getGetSingleSqlSelector()
        ->clearValues()
        ->clearWhereClauses()
        ->addSelectColumn("AES_DECRYPT(students.full_name, '{$this->encryptionKey}') AS studentName")
        ->addSelectColumn("AES_DECRYPT(students.email, '{$this->encryptionKey}') AS studentEmail")
        ->addSelectColumn("COUNT(course_lessons.id) AS lessonCount")
        ->addSelectColumn("count(lessons_results.clid) as doneLessonCount")
        ->addJoin("INNER JOIN students ON students.id = {$this->databaseTable}.student_id")
        ->addJoin("LEFT JOIN course_lessons ON course_lessons.course_id = {$this->databaseTable}.course_id")
        ->addJoin(self::getProgressDataJoinTableSql())
        ->addWhereClause("{$this->getWhereQueryColumnName('course_id')} = ?")
        ->addValue('i', $this->properties->course_id->getValue()->unwrapOr(0));

        if (mb_strlen($searchKeywords) > 3)
        {
            $selector = $selector
            ->addWhereClause(" AND (Convert(AES_DECRYPT(students.full_name, '{$this->encryptionKey}') using 'utf8mb4') like ?
            OR Convert(AES_DECRYPT(students.email, '{$this->encryptionKey}') using 'utf8mb4') like ? )")
            ->addValues('ss', [ "%$searchKeywords%", "%$searchKeywords%" ]);
        }

        $selector = $selector->setOrderBy(match($orderBy)
        {
            'id' => "{$this->databaseTable}.id DESC",
            'name' => "studentName ASC",
            'email' => "studentEmail ASC",
            'datetime' => "{$this->databaseTable}.datetime DESC",
            default => "{$this->databaseTable}.datetime DESC"
        });

        if ($page && $numResultsOnPage)
        {
            $calcPage = ($page - 1) * $numResultsOnPage;
            $selector = $selector
            ->setLimit('?, ?')
            ->addValues('ii', [ $calcPage, $numResultsOnPage ]);
        }

        $selector = $selector->setGroupBy("{$this->databaseTable}.id");

        $drs = $selector->run($conn, SqlSelector::RETURN_ALL_ASSOC);
        return array_map([ $this, 'newInstanceFromDataRowFromDatabase' ], $drs);
    }

    public function getSingleFromStudent(mysqli $conn): static
    {
        $selector = $this->getGetSingleSqlSelector()
        ->addSelectColumn("COUNT(course_lessons.id) AS lessonCount")
        ->addSelectColumn("count(lessons_results.clid) as doneLessonCount")
        ->addSelectColumn("SUM(course_lessons.completion_points) AS maxPoints")
        ->addSelectColumn("sum(lessons_results.completion_points) as studentPoints")
        ->addJoin("INNER JOIN courses ON courses.id = {$this->databaseTable}.course_id")
        ->addJoin("LEFT JOIN course_lessons ON course_lessons.course_id = {$this->databaseTable}.course_id")
        ->addJoin(self::getProgressDataJoinTableSql())
        ->addWhereClause("AND {$this->getWhereQueryColumnName('student_id')} = ?")
        ->addValue('i', $this->properties->student_id->getValue()->unwrapOr(0));

        $dr = $selector->run($conn, SqlSelector::RETURN_SINGLE_ASSOC);
        if (isset($dr))
            return $this->newInstanceFromDataRowFromDatabase($dr);
        else
            throw new DatabaseEntityNotFound("Inscrição não encontrada!", $this->databaseTable);
    }

    public function getAllFromStudentWithProgressData(mysqli $conn) : array
    {
        $selector = $this->getGetSingleSqlSelector()
        ->clearValues()
        ->clearWhereClauses()
        ->addSelectColumn("COUNT(course_lessons.id) AS lessonCount")
        ->addSelectColumn("count(lessons_results.clid) as doneLessonCount")
        ->addSelectColumn("SUM(course_lessons.completion_points) AS maxPoints")
        ->addSelectColumn("sum(lessons_results.completion_points) as studentPoints")
        ->addJoin("INNER JOIN courses ON courses.id = {$this->databaseTable}.course_id")
        ->addJoin("LEFT JOIN course_lessons ON course_lessons.course_id = {$this->databaseTable}.course_id")
        ->addJoin(self::getProgressDataJoinTableSql())
        ->addWhereClause($this->getWhereQueryColumnName('student_id') . ' = ?')
        ->addValue('i', $this->properties->student_id->getValue()->unwrapOr(0))
        ->setGroupBy("{$this->databaseTable}.id");

        $dr = $selector->run($conn, SqlSelector::RETURN_ALL_ASSOC);
        return array_map([$this, 'newInstanceFromDataRow'], $dr ?? []);
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
        ->addSelectColumn("COUNT(course_lessons.id) AS lessonCount")
        ->addSelectColumn("count(lessons_results.clid) as doneLessonCount")
        ->addSelectColumn("SUM(course_lessons.completion_points) AS maxPoints")
        ->addSelectColumn("sum(lessons_results.completion_points) as studentPoints")
        ->addJoin("INNER JOIN courses ON courses.id = {$this->databaseTable}.course_id")
        ->addJoin("INNER JOIN students ON students.id = {$this->databaseTable}.student_id")
        ->addJoin("LEFT JOIN course_lessons ON course_lessons.course_id = {$this->databaseTable}.course_id")
        ->addJoin(self::getProgressDataJoinTableSql())
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

    public function getAllFromCourseForReport(mysqli $conn, string $searchKeywords, string $orderBy) : array
    {
        $selector = $this->getGetSingleSqlSelector()
        ->clearValues()
        ->clearWhereClauses()
        ->addSelectColumn("AES_DECRYPT(students.full_name, '{$this->encryptionKey}') AS studentName")
        ->addSelectColumn("AES_DECRYPT(students.email, '{$this->encryptionKey}') AS studentEmail")
        ->addSelectColumn("JSON_UNQUOTE(JSON_EXTRACT(Convert(AES_DECRYPT(students.other_data, '{$this->encryptionKey}') using 'utf8mb4'), '$.telephone')) AS studentTelephone")
        ->addSelectColumn("COUNT(course_lessons.id) AS lessonCount")
        ->addSelectColumn("count(lessons_results.clid) as doneLessonCount")
        ->addJoin("INNER JOIN students ON students.id = {$this->databaseTable}.student_id")
        ->addJoin("LEFT JOIN course_lessons ON course_lessons.course_id = {$this->databaseTable}.course_id")
        ->addJoin(self::getProgressDataJoinTableSql())
        ->addWhereClause("{$this->getWhereQueryColumnName('course_id')} = ?")
        ->addValue('i', $this->properties->course_id->getValue()->unwrapOr(0));

        if (mb_strlen($searchKeywords) > 3)
        {
            $selector = $selector
            ->addWhereClause(" AND (Convert(AES_DECRYPT(students.full_name, '{$this->encryptionKey}') using 'utf8mb4') like ?
            OR Convert(AES_DECRYPT(students.email, '{$this->encryptionKey}') using 'utf8mb4') like ? )")
            ->addValues('ss', [ "%$searchKeywords%", "%$searchKeywords%" ]);
        }

        $selector = $selector->setOrderBy(match($orderBy)
        {
            'id' => "{$this->databaseTable}.id DESC",
            'name' => "studentName ASC",
            'email' => "studentEmail ASC",
            'datetime' => "{$this->databaseTable}.datetime DESC",
            default => "{$this->databaseTable}.datetime DESC"
        });

        $selector->setGroupBy("{$this->databaseTable}.id");

        $drs = $selector->run($conn, SqlSelector::RETURN_ALL_ASSOC);
        return array_map([ $this, 'newInstanceFromDataRowFromDatabase' ], $drs);
    }

    public function fetchCourse(mysqli $conn) : self
    {
        try
        {
            $this->course = (new Course([ 'id' => $this->properties->course_id->getValue()->unwrapOr(0) ]))->getSingle($conn);
        }
        catch (Exception) {}
        return $this;
    }

    public function fetchStudent(mysqli $conn) : self
    {
        try
        {
            $this->student = (new Student([ 'id' => $this->properties->student_id->getValue()->unwrapOr(0) ]))
            ->setCryptKey($this->encryptionKey)
            ->getSingle($conn);
        }
        catch (Exception) {}
        return $this;
    }

    private static function getProgressDataJoinTableSql() : string
    {
        $passwordPresenceMethods = PresenceMethod::sqlListPassword();
        $testPresenceMethods = PresenceMethod::sqlListTest();
        $neverPresenceMethod = PresenceMethod::sqlListNever();

        return <<<SQL
        left join
            (
                select 
                ss.course_id cid,
                cl.id clid, 
                cl.completion_points,
                if(cl.presence_method in ($passwordPresenceMethods), sum(slp.is_correct) >= 1, 1) as pass_correct, 
                if(cl.presence_method in ($testPresenceMethods), sum(tc.is_approved) >= 1, 1) as test_correct, 
                if(cl.presence_method in ($neverPresenceMethod), 0, 1) as auto_correct
                from student_subscriptions ss 
                inner join course_lessons cl on cl.course_id = ss.course_id 
                left join student_lesson_passwords slp on slp.lesson_id = cl.id
                left join tests_completed tc on tc.lesson_id  = cl.id
                group by cl.id
                having pass_correct and test_correct and auto_correct
            ) as lessons_results on lessons_results.clid = course_lessons.id 
        SQL;
    }
}