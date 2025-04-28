<?php
namespace VictorOpusculo\Parlaflix\Lib\Model\Students;

use Exception;
use mysqli;
use VictorOpusculo\Parlaflix\Lib\Exceptions\LessonPasswordIncorrect;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Lesson;
use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataProperty;
use VOpus\PhpOrm\Exceptions\DatabaseEntityNotFound;
use VOpus\PhpOrm\SqlSelector;
use VOpus\PhpOrm\Option;


/**
 * @property Option<int> id
 * @property Option<int> student_id
 * @property Option<int> lesson_id
 * @property Option<string> given_password
 * @property Option<int> is_correct
 */
final class StudentLessonPassword extends DataEntity
{
    public function __construct(?array $initialValues = null)
    {
        $this->properties = (object)
        [
            'id' => new DataProperty('id', fn() => null, DataProperty::MYSQL_INT),
            'student_id' => new DataProperty('student_id', fn() => null, DataProperty::MYSQL_INT),
            'lesson_id' => new DataProperty('lesson_id', fn() => null, DataProperty::MYSQL_INT),
            'given_password' => new DataProperty('given_password', fn() => null, DataProperty::MYSQL_STRING),
            'is_correct' => new DataProperty('is_correct', fn() => null, DataProperty::MYSQL_INT),
        ];

        parent::__construct($initialValues);
    }

    protected string $databaseTable = 'student_lesson_passwords';
    protected string $formFieldPrefixName = 'student_lesson_passwords';
    protected array $primaryKeys = ['id'];

    public function existsByStudentAndLessonId(mysqli $conn) : bool
    {
        $selector = (new SqlSelector)
        ->addSelectColumn('COUNT(*)')
        ->setTable($this->databaseTable)
        ->addWhereClause("{$this->getWhereQueryColumnName('student_id')} = ?")
        ->addWhereClause("AND {$this->getWhereQueryColumnName('lesson_id')} = ?")
        ->addValue('i', $this->properties->student_id->getValue()->unwrapOr(0) )
        ->addValue('i', $this->properties->lesson_id->getValue()->unwrapOr(0) );

        $count = (int)$selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);
        return $count > 0;
    }

    public function getSingleByStudentAndLessonId(mysqli $conn) : self
    {
        $selector = $this->getGetSingleSqlSelector()
        ->clearValues()
        ->clearWhereClauses()
        ->addWhereClause("{$this->getWhereQueryColumnName('student_id')} = ?")
        ->addWhereClause("AND {$this->getWhereQueryColumnName('lesson_id')} = ?")
        ->addValue('i', $this->properties->student_id->getValue()->unwrapOr(0) )
        ->addValue('i', $this->properties->lesson_id->getValue()->unwrapOr(0) );

        $dr = $selector->run($conn, SqlSelector::RETURN_SINGLE_ASSOC);
        if (isset($dr))
            return $this->newInstanceFromDataRowFromDatabase($dr);
        else
            throw new DatabaseEntityNotFound("Registro de presença não encontrado!", $this->databaseTable);
    }

    public function beforeDatabaseInsert(mysqli $conn): int
    {
        if (!isset($_SESSION['user_timezone']))
            throw new Exception("Sessão de login não tem informação de fuso horário!");

        $lesson = (new Lesson([ 'id' => $this->properties->lesson_id->getValue()->unwrapOr(0) ]))
        ->getSingle($conn)
        ->informDateTimeZone($_SESSION['user_timezone']);
        
        $isCorrect = $lesson->isPasswordCorrect($this->properties->given_password->getValue()->unwrapOr(''));
        $this->properties->is_correct->setValue($isCorrect ? 1 : 0);

        if (!$isCorrect)
            throw new LessonPasswordIncorrect("Senha de aula incorreta!", $this->properties->lesson_id->getValue()->unwrapOr(0));

        return 0;
    }

    /** @return StudentLessonPassword[] */
    public function getAllByLesson(mysqli $conn) : array
    {
        $selector = $this->getGetSingleSqlSelector()
        ->clearValues()
        ->clearWhereClauses()
        ->addWhereClause("{$this->getWhereQueryColumnName('lesson_id')} = ?")
        ->addSelectColumn("AES_DECRYPT(students.full_name, '{$this->encryptionKey}') AS studentName")
        ->addSelectColumn("AES_DECRYPT(students.email, '{$this->encryptionKey}') AS studentEmail")
        ->addValue('i', $this->properties->lesson_id->getValue()->unwrapOr(0) )
        ->addJoin("INNER JOIN students ON students.id = {$this->databaseTable}.student_id");

        $drs = $selector->run($conn, SqlSelector::RETURN_ALL_ASSOC);
        return array_map([ $this, 'newInstanceFromDataRowFromDatabase'], $drs);
    }
}