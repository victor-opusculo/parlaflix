<?php

namespace VictorOpusculo\Parlaflix\Lib\Model\Courses;

use mysqli;
use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataProperty;
use VOpus\PhpOrm\SqlSelector;

class Lesson extends DataEntity
{
    public function __construct(?array $initialValues = null)
    {
        $this->properties = (object)
        [
            'id' => new DataProperty(null, fn() => null, DataProperty::MYSQL_INT),
            'course_id' => new DataProperty('course_id', fn() => null, DataProperty::MYSQL_INT),
            'index' => new DataProperty('index', fn() => null, DataProperty::MYSQL_INT),
            'title' => new DataProperty('title', fn() => 'Aula sem nome', DataProperty::MYSQL_STRING),
            'presentation_html' => new DataProperty('presentation_html', fn() => null, DataProperty::MYSQL_STRING),
            'video_host' => new DataProperty('video_host', fn() => null, DataProperty::MYSQL_STRING),
            'video_url' => new DataProperty('video_url', fn() => null, DataProperty::MYSQL_STRING),
            'completion_password' => new DataProperty('completion_password', fn() => null, DataProperty::MYSQL_STRING),
            'completion_points' => new DataProperty('completion_points', fn() => null, DataProperty::MYSQL_INT)
        ];

        parent::__construct($initialValues);
    }

    protected string $databaseTable = 'course_lessons';
    protected string $formFieldPrefixName = 'course_lessons';
    protected array $primaryKeys = ['id']; 

    public function getAllFromCourse(mysqli $conn) : array
    {
        $selector = $this->getGetSingleSqlSelector()
        ->clearValues()
        ->clearWhereClauses()
        ->addWhereClause("{$this->getWhereQueryColumnName('course_id')} = ?")
        ->addValue('i', $this->properties->course_id->getValue()->unwrapOr(0))
        ->setOrderBy('`index` ASC');

        $drs = $selector->run($conn, SqlSelector::RETURN_ALL_ASSOC);
        return array_map([ $this, 'newInstanceFromDataRow'], $drs);
    }
}