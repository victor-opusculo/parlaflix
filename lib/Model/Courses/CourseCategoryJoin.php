<?php

namespace VictorOpusculo\Parlaflix\Lib\Model\Courses;

use mysqli;
use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataProperty;
use VOpus\PhpOrm\SqlSelector;

class CourseCategoryJoin extends DataEntity
{
    public function __construct(?array $initialValues = null)
    {
        $this->properties = (object)
        [
            'course_id' => new DataProperty(null, fn() => null, DataProperty::MYSQL_INT),
            'category_id' => new DataProperty(null, fn() => null, DataProperty::MYSQL_INT)
        ];

        parent::__construct($initialValues);
    }

    protected string $databaseTable = 'courses_categories_join';
    protected string $formFieldPrefixName = 'courses_categories_join';
    protected array $primaryKeys = ['course_id', 'category_id']; 
    protected array $setPrimaryKeysValue = [ 'course_id', 'category_id' ];

    public function saveCategoriesOfCourseId(mysqli $conn, int $courseId, array $categoriesIds) : int
    {
        $categoriesIdsSaved = (new SqlSelector)
        ->addSelectColumn('category_id')
        ->setTable($this->databaseTable)
        ->addWhereClause("{$this->getWhereQueryColumnName('course_id')} = ?")
        ->addValue('i', $courseId)
        ->run($conn, SqlSelector::RETURN_ALL_ASSOC);

        $IdsSaved = array_map(fn($dr) => (int)$dr['category_id'], $categoriesIdsSaved);
        $toDelete = [];
        $toCreate = [];
        
        foreach ($IdsSaved as $savedId)
        {
            if (array_search($savedId, $categoriesIds) === false)
                $toDelete[] = new self([ 'category_id' => $savedId, 'course_id' => $courseId ]);
        }

        foreach ($categoriesIds as $catId)
        {
            if (array_search($catId, $IdsSaved) === false)
                $toCreate[] = new self([ 'category_id' => $catId, 'course_id' => $courseId ]);
        }

        $deletedRows = array_reduce($toDelete, fn($carry, $joint) => $joint->delete($conn)['affectedRows'] + $carry, 0);
        $createdRows = array_reduce($toCreate, fn($carry, $joint) => $joint->save($conn)['affectedRows'] + $carry, 0);

        return $deletedRows + $createdRows;
    }

    public function getAllFromCourse(mysqli $conn) : array
    {
        $selector = $this->getGetSingleSqlSelector()
        ->addSelectColumn('categories.title AS title')
        ->clearValues()
        ->clearWhereClauses()
        ->addJoin("INNER JOIN categories ON categories.id = {$this->databaseTable}.category_id")
        ->addWhereClause("{$this->getWhereQueryColumnName('course_id')} = ?")
        ->addValue('i', $this->properties->course_id->getValue()->unwrapOr(0));

        $drs = $selector->run($conn, SqlSelector::RETURN_ALL_ASSOC);
        return array_map([ $this, 'newInstanceFromDataRow' ], $drs);
    }
}