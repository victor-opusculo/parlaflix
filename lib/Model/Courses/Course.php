<?php

namespace VictorOpusculo\Parlaflix\Lib\Model\Courses;

use mysqli;
use VictorOpusculo\Parlaflix\Lib\Model\Media\Media;
use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataProperty;
use VOpus\PhpOrm\EntitiesChangesReport;
use VOpus\PhpOrm\Exceptions\DatabaseEntityNotFound;
use VOpus\PhpOrm\SqlSelector;
use VOpus\PhpOrm\Option;

class Course extends DataEntity
{
    public function __construct(?array $initialValues = null)
    {
        $this->properties = (object)
        [
            'id' => new DataProperty('id', fn() => null, DataProperty::MYSQL_INT),
            'name' => new DataProperty('name', fn() => 'Sem nome definido', DataProperty::MYSQL_STRING),
            'presentation_html' => new DataProperty('presentation_html', fn() => null, DataProperty::MYSQL_STRING),
            'cover_image_media_id' => new DataProperty('cover_image_media_id', fn() => null, DataProperty::MYSQL_INT),
            'hours' => new DataProperty('hours', fn() => 0, DataProperty::MYSQL_DOUBLE),
            'certificate_text' => new DataProperty('certificate_text', fn() => null, DataProperty::MYSQL_STRING),
            'min_points_required' => new DataProperty('min_points_required', fn() => 0, DataProperty::MYSQL_INT),
            'is_visible' => new DataProperty('is_visible', fn() => 0, DataProperty::MYSQL_INT),
            'created_at' => new DataProperty('created_at', fn() => gmdate('Y-m-d H:i:s'), DataProperty::MYSQL_STRING)
        ];

        parent::__construct($initialValues);
    }

    protected string $databaseTable = 'courses';
    protected string $formFieldPrefixName = 'courses';
    protected array $primaryKeys = ['id'];
    
    public array $lessons = [];
    public array $categoriesJoints = [];
    public ?Media $coverMedia = null;

    protected ?string $dateTimeZone = null;

    public function informDateTimeZone(string $dtz) : self
    {
        $this->dateTimeZone = $dtz;
        return $this;
    }

    public function getCount(mysqli $conn, string $searchKeywords, bool $includeNonVisible = true, ?int $categoryId = null) : int
    {
        $selector = (new SqlSelector)
        ->addSelectColumn("COUNT(DISTINCT {$this->databaseTable}.id)")
        ->setTable($this->databaseTable)
        ->addJoin("LEFT JOIN courses_categories_join ON courses_categories_join.course_id = {$this->databaseTable}.id");

        if (mb_strlen($searchKeywords) > 3)
        {
            $selector = $selector
            ->addWhereClause("MATCH (name) AGAINST (?) ")
            ->addValue('s', $searchKeywords);
        }

        if (!$includeNonVisible)
        {
            $selector = 
            $selector->hasWhereClauses() ? 
                $selector->addWhereClause("AND {$this->getWhereQueryColumnName('is_visible')} = 1") :
                $selector->addWhereClause("{$this->getWhereQueryColumnName('is_visible')} = 1");
        }

        if ($categoryId)
        {
            $selector = $selector->hasWhereClauses()
                ? $selector->addWhereClause("AND courses_categories_join.category_id = ?")->addValue('i', $categoryId)
                : $selector->addWhereClause("courses_categories_join.category_id = ?")->addValue('i', $categoryId);
        }

        $count = (int)$selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);
        return $count;
    }

    public function getMultiple(mysqli $conn, string $searchKeywords, string $orderBy, int $page, int $numResultsOnPage, bool $includeNonVisible = true, ?int $categoryId = null) : array
    {
        $selector = $this->getGetSingleSqlSelector()
        ->addJoin("LEFT JOIN courses_categories_join ON courses_categories_join.course_id = {$this->databaseTable}.id")
        ->clearWhereClauses()
        ->clearValues();

        if (mb_strlen($searchKeywords) > 3)
        {
            $selector = $selector
            ->addWhereClause("MATCH (name) AGAINST (?) ")
            ->addValue('s', $searchKeywords);
        }

        if (!$includeNonVisible)
        {
            $selector = 
            $selector->hasWhereClauses() ? 
                $selector->addWhereClause("AND {$this->getWhereQueryColumnName('is_visible')} = 1") :
                $selector->addWhereClause("{$this->getWhereQueryColumnName('is_visible')} = 1");
        }

        if ($categoryId)
        {
            $selector = $selector->hasWhereClauses()
                ? $selector->addWhereClause("AND courses_categories_join.category_id = ?")->addValue('i', $categoryId)
                : $selector->addWhereClause("courses_categories_join.category_id = ?")->addValue('i', $categoryId);
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
        ->addValues('ii', [ $calcPage, $numResultsOnPage ])
        ->setGroupBy("{$this->databaseTable}.id");

        $drs = $selector->run($conn, SqlSelector::RETURN_ALL_ASSOC);
        return array_map([ $this, 'newInstanceFromDataRow' ], $drs);
    }

    public function getSingleVisibleOnly(mysqli $conn) : self
    {
        $selector = $this->getGetSingleSqlSelector()
        ->addWhereClause("AND {$this->getWhereQueryColumnName('is_visible')} = 1");

        $dr = $selector->run($conn, SqlSelector::RETURN_SINGLE_ASSOC);

        if (isset($dr))
            return $this->newInstanceFromDataRow($dr);
        else
            throw new DatabaseEntityNotFound("Curso não encontrado!", $this->databaseTable);
    }

    public function fetchLessons(mysqli $conn) : self
    {
        $this->lessons = (new Lesson([ 'course_id' => $this->properties->id->getValue()->unwrapOr(0) ]))->getAllFromCourse($conn);
        foreach ($this->lessons as $less)
            $less->informDateTimeZone($this->dateTimeZone);

        return $this;
    }

    public function fetchCategoriesJoints(mysqli $conn) : self
    {
        $this->categoriesJoints = (new CourseCategoryJoin([ 'course_id' => $this->properties->id->getValue()->unwrapOr(0) ]))->getAllFromCourse($conn);
        return $this;
    }

    public function fetchCoverMedia(mysqli $conn) : self
    {
        if (!$this->properties->cover_image_media_id->getValue()->unwrapOr(false))
            return $this;

        $this->coverMedia = (new Media([ 'id' => $this->properties->cover_image_media_id->getValue()->unwrap() ]))->getSingle($conn);
        return $this;
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

    public function beforeDatabaseInsert(mysqli $conn): int
    {
        $this->properties->created_at->setValue(gmdate('Y-m-d H:i:s'));
        return 0;
    }

    public function afterDatabaseInsert(mysqli $conn, $insertResult)
    {
        $creportData = $this->otherProperties->lessonsChangesReport ?? [ 'create' => [], 'update' => [], 'delete' => [] ];
        $creport = new EntitiesChangesReport($creportData, Lesson::class);

        $creport->setPropertyValueForAll('course_id', $insertResult['newId']);
        $creport->callMethodForAll('informDateTimeZone', $_SESSION['user_timezone']);
        $insertResult['affectedRows'] += $creport->applyToDatabase($conn);

        $categoriesIds = $this->otherProperties->categoriesIds ?? [];
        $insertResult['affectedRows'] += (new CourseCategoryJoin)->saveCategoriesOfCourseId($conn, $insertResult['newId'], $categoriesIds);

        return $insertResult;
    }

    public function afterDatabaseUpdate(mysqli $conn, $updateResult)
    {
        $creportData = $this->otherProperties->lessonsChangesReport ?? [ 'create' => [], 'update' => [], 'delete' => [] ];
        $creport = new EntitiesChangesReport($creportData, Lesson::class);

        $creport->setPropertyValueForAll('course_id', $this->properties->id->getValue()->unwrap() );
        $creport->callMethodForAll('informDateTimeZone', $_SESSION['user_timezone']);
        $updateResult['affectedRows'] += $creport->applyToDatabase($conn);

        $categoriesIds = $this->otherProperties->categoriesIds ?? [];
        $updateResult['affectedRows'] += (new CourseCategoryJoin)->saveCategoriesOfCourseId($conn, $this->properties->id->getValue()->unwrap(), $categoriesIds);

        return $updateResult;
    }
}