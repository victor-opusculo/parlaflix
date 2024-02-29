<?php

namespace VictorOpusculo\Parlaflix\Lib\Model\Courses;

use mysqli;
use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataProperty;
use VOpus\PhpOrm\SqlSelector;

class Course extends DataEntity
{
    public function __construct(?array $initialValues = null)
    {
        $this->properties = (object)
        [
            'id' => new DataProperty(null, fn() => null, DataProperty::MYSQL_INT),
            'name' => new DataProperty('name', fn() => 'Sem nome definido', DataProperty::MYSQL_STRING),
            'presentation_html' => new DataProperty('presentationHtml', fn() => null, DataProperty::MYSQL_STRING),
            'cover_image_media_id' => new DataProperty('coverImageMediaId', fn() => null, DataProperty::MYSQL_INT),
            'hours' => new DataProperty('hours', fn() => 0, DataProperty::MYSQL_DOUBLE),
            'certificate_text' => new DataProperty('certificateText', fn() => null, DataProperty::MYSQL_STRING),
            'min_points_required' => new DataProperty('numRequiredPoints', fn() => 0, DataProperty::MYSQL_INT),
            'is_visible' => new DataProperty('isVisible', fn() => 0, DataProperty::MYSQL_INT),
            'created_at' => new DataProperty('createdAt', fn() => gmdate('Y-m-d H:i:s'), DataProperty::MYSQL_STRING)
        ];

        parent::__construct($initialValues);
    }

    protected string $databaseTable = 'courses';
    protected string $formFieldPrefixName = 'courses';
    protected array $primaryKeys = ['id'];
    
    public function getCount(mysqli $conn, string $searchKeywords) : int
    {
        $selector = (new SqlSelector)
        ->addSelectColumn('COUNT(*)')
        ->setTable($this->databaseTable);

        if (mb_strlen($searchKeywords) > 3)
        {
            $selector = $selector
            ->addWhereClause("MATCH (name) AGAINST (?) ")
            ->addValue('s', $searchKeywords);
        }

        $count = (int)$selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);
        return $count;
    }

    public function getMultiple(mysqli $conn, string $searchKeywords, string $orderBy, int $page, int $numResultsOnPage) : array
    {
        $selector = $this->getGetSingleSqlSelector()
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
            'name' => 'name ASC',
            'hours' => 'hours ASC',
            'created_at' => 'created_at DESC',
            'id' => 'id DESC',
            default => 'id DESC'
        });

        $calcPage = ($page - 1) * $numResultsOnPage;
        $selector = $selector
        ->setLimit('?,?')
        ->addValues('ii', [ $calcPage, $numResultsOnPage ]);

        $drs = $selector->run($conn, SqlSelector::RETURN_ALL_ASSOC);
        return array_map([ $this, 'newInstanceFromDataRow' ], $drs);
    }

    public function exists(mysqli $conn) : bool
    {
        $selector = (new SqlSelector)
        ->addSelectColumn('COUNT(*)')
        ->setTable($this->databaseTable)
        ->addWhereClause("{$this->getWhereQueryColumnName('id')} = ?")
        ->addValue('i', $this->properties->id->getValue()->unwrap());

        $exists = $selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);
        return $exists > 0;
    }

    public function getAll(mysqli $conn) : array
    {
        $selector = $this->getGetSingleSqlSelector()
        ->clearValues()
        ->clearWhereClauses();

        $drs = $selector->run($conn, SqlSelector::RETURN_ALL_ASSOC);
        return array_map([ $this, 'newInstanceFromDataRow' ], $drs);
    }

    public function getInfos(mysqli $conn) : array
    {
        $selector = (new SqlSelector)
        ->addSelectColumn('courses.hours')
        ->addSelectColumn('COUNT(DISTINCT course_modules.id) AS modules')
        ->addSelectColumn('COUNT(DISTINCT course_lessons.id) AS lessons')
        ->addSelectColumn('COUNT(DISTINCT course_tests.id) as tests')
        ->addSelectColumn('COUNT(DISTINCT course_lesson_block.id) AS blocks')
        ->setTable($this->databaseTable)
        ->addJoin("LEFT JOIN course_modules ON course_modules.course_id = courses.id")
        ->addJoin("LEFT JOIN course_lessons ON course_lessons.module_id = course_modules.id")
        ->addJoin("LEFT JOIN course_tests ON course_tests.course_id = courses.id")
        ->addJoin("LEFT JOIN course_lesson_block ON course_lesson_block.lesson_id = course_lessons.id")
        ->addWhereClause("{$this->getWhereQueryColumnName('id')} = ?")
        ->addValue('i', $this->properties->id->getValue()->unwrapOr(0));

        $dr = $selector->run($conn, SqlSelector::RETURN_SINGLE_ASSOC);
        return $dr;
    }
}