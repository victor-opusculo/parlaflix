<?php
namespace VictorOpusculo\Parlaflix\Lib\Model\Students;

use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataProperty;

final class StudentLessonPassword extends DataEntity
{
    public function __construct(?array $initialValues = null)
    {
        $this->properties = (object)
        [
            'student_id' => new DataProperty('studentId', fn() => null, DataProperty::MYSQL_INT),
            'lesson_id' => new DataProperty('lessonId', fn() => null, DataProperty::MYSQL_INT),
            'given_password' => new DataProperty('givenPassword', fn() => null, DataProperty::MYSQL_STRING),
            'is_correct' => new DataProperty('isCorrect', fn() => null, DataProperty::MYSQL_INT),
        ];

        parent::__construct($initialValues);
    }

    protected string $databaseTable = 'student_lesson_passwords';
    protected string $formFieldPrefixName = 'student_lesson_passwords';
    protected array $primaryKeys = ['id'];
}