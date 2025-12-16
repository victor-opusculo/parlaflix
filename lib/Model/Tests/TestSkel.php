<?php

namespace VictorOpusculo\Parlaflix\Lib\Model\Tests;

use Exception;
use JsonException;
use mysqli;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
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
            'min_percent_for_approval' => new DataProperty('min_percent_for_approval', fn() => self::DEFAULT_MIN_PERCENT, DataProperty::MYSQL_INT, false)
        ];

        parent::__construct($initialValues);
    }

    public const DB_TABLE = 'tests_skel';
    public const DEFAULT_MIN_PERCENT = 70;

    protected string $databaseTable = self::DB_TABLE;
    protected string $formFieldPrefixName = self::DB_TABLE;
    protected array $primaryKeys = ['id']; 

    public private(set) ?Lesson $lesson = null;

    public function getFromLessonId(mysqli $conn) : self
    {
        $selector = $this->getGetSingleSqlSelector()
        ->clearWhereClauses()
        ->clearValues()
        ->addWhereClause("{$this->getWhereQueryColumnName('lesson_id')} = ?")
        ->addValue('i', $this->lesson_id->unwrapOr(0));

        $dr = $selector->run($conn, SqlSelector::RETURN_SINGLE_ASSOC);

        if (isset($dr))
            return $this->newInstanceFromDataRowFromDatabase($dr);
        else
            throw new DatabaseEntityNotFound("Modelo de questionário não encontrado!", $this->databaseTable);
    }

    public function fetchLesson(mysqli $conn) : self
    {
        $this->lesson = new Lesson([ 'id' => $this->lesson_id->unwrapOr(0) ])->getSingle($conn);
        return $this;
    }

    public function buildStructure() : TestData
    {
        try
        {
            $json = $this->test_data->unwrapOr('{}');
            $struct = TestData::buildFromJson($json);
            return $struct;
        }
        catch (JsonException $e)
        {
            throw new InvalidConfigurationException("JSON de questionário inválido!");
        }
        catch (Exception $e)
        {
            throw $e;
        }
    }
}





