<?php
namespace VictorOpusculo\Parlaflix\Lib\Model\Tests;

use mysqli;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Lesson;
use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataProperty;
use VOpus\PhpOrm\Option;

/**
 * @property Option<int> id
 * @property Option<int> subscription_id
 * @property Option<int> test_skel_id
 * @property Option<int> lesson_id
 * @property Option<string> test_data
 * @property Option<int> is_approved
 */
class TestCompleted extends DataEntity
{
    public function __construct(?array $initialValues = null)
    {
        $this->properties = (object)
        [
            'id' => new DataProperty('id', fn() => null, DataProperty::MYSQL_INT, false),
            'subscription_id' => new DataProperty('subscription_id', fn() => null, DataProperty::MYSQL_INT, false),
            'test_skel_id' => new DataProperty('test_skel_id', fn() => null, DataProperty::MYSQL_INT, false),
            'lesson_id' => new DataProperty('lesson_id', fn() => null, DataProperty::MYSQL_INT, false),
            'test_data' => new DataProperty('test_data', fn() => "{}", DataProperty::MYSQL_STRING, false),
            'is_approved' => new DataProperty('is_approved', fn() => 0, DataProperty::MYSQL_INT, false),
        ];

        parent::__construct($initialValues);
    }

    protected string $databaseTable = 'tests_completed';
    protected string $formFieldPrefixName = 'tests_completed';
    protected array $primaryKeys = ['id']; 

    public private(set) ?Lesson $lesson = null;
    public private(set) ?TestSkel $skel = null;

    public function fetchLesson(mysqli $conn) : self
    {
        $this->lesson = new Lesson([ 'id' => $this->lesson_id->unwrapOr(0) ])->getSingle($conn);
        return $this;
    }

    public function fetchSkel(mysqli $conn) : self
    {
        $this->skel = new TestSkel([ 'id' => $this->test_skel_id->unwrapOr(0) ])->getSingle($conn);
        return $this;
    }
}
