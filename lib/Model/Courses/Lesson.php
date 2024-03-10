<?php

namespace VictorOpusculo\Parlaflix\Lib\Model\Courses;

use DateTime;
use DateTimeZone;
use mysqli;
use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataProperty;
use VOpus\PhpOrm\Option;
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
            'live_meeting_url' => new DataProperty('live_meeting_url', fn() => null, DataProperty::MYSQL_STRING),
            'live_meeting_datetime' => new DataProperty('live_meeting_datetime', fn() => null, DataProperty::MYSQL_STRING),
            'video_host' => new DataProperty('video_host', fn() => null, DataProperty::MYSQL_STRING),
            'video_url' => new DataProperty('video_url', fn() => null, DataProperty::MYSQL_STRING),
            'completion_password' => new DataProperty('completion_password', fn() => null, DataProperty::MYSQL_STRING),
            'completion_points' => new DataProperty('completion_points', fn() => null, DataProperty::MYSQL_INT)
        ];

        $this->properties->live_meeting_datetime->setValueTransformer = fn($dtStr) =>
            $dtStr
            ?   (new DateTime($dtStr, new DateTimeZone($this->dateTimeZone ?? 'America/Sao_Paulo')))
                ->setTimezone(new DateTimeZone('UTC'))
                ->format('Y-m-d H:i:s')
            :   null;

        $this->properties->live_meeting_datetime->valueTransformer = fn($dtStr) =>
            $dtStr->unwrapOr(null)
            ?   Option::some((new DateTime($dtStr->unwrapOr('now'), new DateTimeZone('UTC')))
                ->setTimezone(new DateTimeZone($this->dateTimeZone ?? 'America/Sao_Paulo'))
                ->format('c'))
            :   Option::some(null);

        $this->properties->live_meeting_datetime->valueTransformerForDatabase = fn($dtOpt) => $dtOpt;
        $this->properties->live_meeting_datetime->setValueTransformerFromDatabase = fn($dtOpt) => $dtOpt;

        parent::__construct($initialValues);
    }

    protected string $databaseTable = 'course_lessons';
    protected string $formFieldPrefixName = 'course_lessons';
    protected array $primaryKeys = ['id']; 

    protected ?string $dateTimeZone = null;

    public function informDateTimeZone(string $dtz) : self
    {
        $this->dateTimeZone = $dtz;
        return $this;
    }

    public function getAllFromCourse(mysqli $conn) : array
    {
        $selector = $this->getGetSingleSqlSelector()
        ->clearValues()
        ->clearWhereClauses()
        ->addWhereClause("{$this->getWhereQueryColumnName('course_id')} = ?")
        ->addValue('i', $this->properties->course_id->getValue()->unwrapOr(0))
        ->setOrderBy('`index` ASC');

        $drs = $selector->run($conn, SqlSelector::RETURN_ALL_ASSOC);
        return array_map([ $this, 'newInstanceFromDataRowFromDatabase'], $drs);
    }

    public function isPasswordCorrect(string $givenPassword) : bool
    {
        if (!$givenPassword)
            throw new \Exception("Senha não informada!");

        $lessPassword = $this->properties->completion_password->getValue()->unwrapOr('');
        return $lessPassword === $givenPassword;
    }

    public function passedLiveMeetingDate() : bool
    {
        if (!$this->properties->live_meeting_datetime->getValue()->unwrapOr(false))
            return true;

        $liveMeetDt = new DateTime($this->properties->live_meeting_datetime->getValue()->unwrap());
        $currentDt = new DateTime('now', new DateTimeZone($this->dateTimeZone));

        return $currentDt >= $liveMeetDt;
    }
}