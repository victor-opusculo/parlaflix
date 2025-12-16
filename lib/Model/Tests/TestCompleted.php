<?php
namespace VictorOpusculo\Parlaflix\Lib\Model\Tests;

use Exception;
use mysqli;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Lesson;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Subscription;
use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataProperty;
use VOpus\PhpOrm\Option;
use VOpus\PhpOrm\SqlSelector;

/**
 * @property Option<int> id
 * @property Option<int> subscription_id
 * @property Option<int> test_skel_id
 * @property Option<int> lesson_id
 * @property Option<string> test_data
 * @property Option<int> is_approved
 * @property Option<string> created_at
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
            'created_at' => new DataProperty('created_at', fn() => gmdate('Y-m-d H:i:s'), DataProperty::MYSQL_STRING, false),
        ];

        parent::__construct($initialValues);
    }

    public const MAX_NUMBER_OF_ATTEMPTS = 5;

    protected string $databaseTable = 'tests_completed';
    protected string $formFieldPrefixName = 'tests_completed';
    protected array $primaryKeys = ['id']; 

    public private(set) ?Subscription $subscription = null;
    public private(set) ?Lesson $lesson = null;
    public private(set) ?TestSkel $skel = null;


    public function getAllFromSubscription(mysqli $conn) : array
    {
        $selector = $this->getGetSingleSqlSelector()
        ->clearValues()
        ->clearWhereClauses()
        ->addWhereClause("{$this->getWhereQueryColumnName('subscription_id')} = ?")
        ->addValue('i', $this->subscription_id->unwrapOr(0));

        $drs = $selector->run($conn, SqlSelector::RETURN_ALL_ASSOC);
        return array_map([ $this, 'newInstanceFromDataRowFromDatabase'], $drs);
    }

    public function studentMaxedAttemps(mysqli $conn) : array
    {
        $selector = new SqlSelector()
        ->setTable($this->databaseTable)
        ->addSelectColumn("COUNT( DISTINCT {$this->getSelectQueryColumnName('id')})")
        ->addWhereClause("{$this->getWhereQueryColumnName('subscription_id')} = ?")
        ->addWhereClause(" AND {$this->getWhereQueryColumnName('lesson_id')} = ?")
        ->addValues('ii', [ $this->subscription_id->unwrap(), $this->lesson_id->unwrap() ]);

        $count = (int)$selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);
        return [ $count >= self::MAX_NUMBER_OF_ATTEMPTS, $count ];
    }

    public function getStudentApprovedTest(mysqli $conn) : self|false
    {
        $selector = $this->getGetSingleSqlSelector()
        ->clearWhereClauses()
        ->clearValues()
        ->addWhereClause("{$this->getWhereQueryColumnName('subscription_id')} = ? ")
        ->addWhereClause(" AND {$this->getWhereQueryColumnName('lesson_id')} = ? ")
        ->addWhereClause(" AND {$this->getWhereQueryColumnName('is_approved')} = 1 ")
        ->addValues('ii', [ $this->subscription_id->unwrap(), $this->lesson_id->unwrap() ]);

        $dr = $selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);

        if (isset($dr))
            return $this->newInstanceFromDataRowFromDatabase($dr);
        else
            return false;
    }

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

    public function fetchSubscription(mysqli $conn) : self
    {
        $this->subscription = new Subscription([ 'id' => $this->subscription_id->unwrapOr(0) ])->getSingle($conn);
        return $this;
    }

    public function beforeDatabaseInsert(mysqli $conn): int
    {
        $this->properties->created_at->resetValue();
        return 0;
    }

    public function buildStructure() : TestData
    {
        try
        {
            $json = $this->test_data->unwrapOr('{}');
            $struct = TestData::buildFromJson($json);
            return $struct;
        }
        catch (\JsonException $e)
        {
            throw new Exception("JSON de questionário inválido!");
        }
        catch (\Exception $e)
        {
            throw $e;
        }
    }
}
