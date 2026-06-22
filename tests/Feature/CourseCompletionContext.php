<?php
namespace Tests\Feature;

use Tests\TestCase;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Lesson;
use VictorOpusculo\Parlaflix\Lib\Model\Students\StudentLessonPassword;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Student;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Subscription;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestSkel;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestCompleted;

final class CourseCompletionContext
{
    public ?Student $student = null;
    public ?Course $course = null;

    /** @var Lesson[] */
    public array $lessons = [];

    public ?Subscription $subscription = null;

    /** @var StudentLessonPassword[] */
    public array $passwordsGiven = [];

    /** @var TestCompleted[] */
    public array $testsCompleted = [];

    /** @var TestSkel[] */
    public array $testsSkel = [];
}