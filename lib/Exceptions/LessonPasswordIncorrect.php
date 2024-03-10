<?php
namespace VictorOpusculo\Parlaflix\Lib\Exceptions;

use Exception;

class LessonPasswordIncorrect extends Exception
{
    public function __construct(string $message, public int $lessonId)
    {
        parent::__construct($message);
    }
}