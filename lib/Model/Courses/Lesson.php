<?php

namespace VictorOpusculo\Parlaflix\Lib\Model\Courses;

use mysqli;
use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataProperty;
use VOpus\PhpOrm\SqlSelector;

class Lesson extends DataEntity
{
    public function __construct(?array $initialValues)
    {
        $this->properties = (object)
        [
            'id' => new DataProperty(null, fn() => null, DataProperty::MYSQL_INT),
            'course_id' => new DataProperty('moduleId', fn() => null, DataProperty::MYSQL_INT),
            'index' => new DataProperty('index', fn() => null, DataProperty::MYSQL_INT),
            'title' => new DataProperty('title', fn() => 'Aula sem nome', DataProperty::MYSQL_STRING),
            'presentation_html' => new DataProperty('presentationHtml', fn() => null, DataProperty::MYSQL_STRING),
            'video_host' => new DataProperty('videoHost', fn() => null, DataProperty::MYSQL_STRING),
            'video_url' => new DataProperty('videoUrl', fn() => null, DataProperty::MYSQL_STRING),
            'completion_password' => new DataProperty('completionPassword', fn() => null, DataProperty::MYSQL_STRING),
            'completion_points' => new DataProperty('completionPoints', fn() => null, DataProperty::MYSQL_INT)
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
        ->setOrderBy('index ASC');

        $drs = $selector->run($conn, SqlSelector::RETURN_ALL_ASSOC);
        return array_map([ $this, 'newInstanceFromDataRow'], $drs);
    }
}