<?php

namespace VictorOpusculo\Parlaflix\Lib\Model\Tests;

use Exception;
use mysqli;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Lesson;
use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataObjectProperty;
use VOpus\PhpOrm\DataProperty;
use VOpus\PhpOrm\Exceptions\DatabaseEntityNotFound;
use VOpus\PhpOrm\Option;
use VOpus\PhpOrm\SqlSelector;

/**
 * @property Option<int> id
 * @property Option<int> lesson_id
 * @property Option<string> name
 * @property Option<string> presentation_text
 * @property Option<string> test_data
 * @property Option<int> min_percent_for_approval
 */
class TestSkel extends DataEntity
{
    public function __construct(?array $initialValues = null)
    {
        $this->properties = (object)
        [
            'id' => new DataProperty('id', fn() => null, DataProperty::MYSQL_INT, false),
            'lesson_id' => new DataProperty('lesson_id', fn() => null, DataProperty::MYSQL_INT, false),
            'name' => new DataProperty('name', fn() => "Teste sem nome", DataProperty::MYSQL_STRING, false),
            'presentation_text' => new DataProperty('presentation_text', fn() => null, DataProperty::MYSQL_STRING, false),
            'test_data' => new DataProperty('test_data', fn() => "{}", DataProperty::MYSQL_STRING, false),
            'min_percent_for_approval' => new DataProperty('min_percent_for_approval', fn() => null, DataProperty::MYSQL_INT, false)
        ];

        parent::__construct($initialValues);
    }

    protected string $databaseTable = 'tests_skel';
    protected string $formFieldPrefixName = 'tests_skel';
    protected array $primaryKeys = ['id']; 

    public private(set) ?Lesson $lesson = null;

    public function fetchLesson(mysqli $conn) : self
    {
        $this->lesson = new Lesson([ 'id' => $this->lesson_id->unwrapOr(0) ])->getSingle($conn);
        return $this;
    }
}





