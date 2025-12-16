<?php
namespace VictorOpusculo\Parlaflix\App\Student\Panel\Subscription;

use Exception;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Lesson;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Subscription;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestCompleted;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestData;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestSkel;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;
use VOpus\PhpOrm\Exceptions\DatabaseEntityNotFound;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class FillTest extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Preencher questionário";

        $conn = Connection::get();
        try
        {
            if (isset($_GET['lesson_id']))
                $this->lessonId = $_GET['lesson_id'] ?? 0;

            if (isset($_GET['back_to_subscription']))
                $this->subscriptionId = $_GET['back_to_subscription'] ?? 0;

            if (!Connection::isId($this->lessonId))
                throw new \Exception('ID inválido!');

            $this->lesson = new Lesson([ 'id' => $this->lessonId ])
            ->getSingle($conn)
            ->informDateTimeZone($_SESSION['user_timezone'] ?? 'America/Sao_Paulo')
            ->fetchCourse($conn);

            $studentId = $_SESSION['user_id'] ?? 0;
            $subscription = new Subscription([ 'id' => $this->subscriptionId, 'student_id' => $studentId ])->getSingleFromStudent($conn);

            [$maxed, $this->attemptNumber] = new TestCompleted([ 'subscription_id' => $subscription->id->unwrap(), 'lesson_id' => $this->lesson->id->unwrap() ])
            ->studentMaxedAttemps($conn);

            if ($maxed)
                throw new Exception("Você atingiu o limite de tentativas!");

            $this->skel = new TestSkel([ 'lesson_id' => $this->lessonId ])
            ->getFromLessonId($conn);

            $this->testJson = $this->skel?->buildStructure($conn)->stripAnswers()->toJson() ?? TestData::empty()->toJson();
        }
        catch (DatabaseEntityNotFound $e)
        {
            Context::getRef('page_messages')[] = "Dados não encontrados!";
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    private ?int $subscriptionId = null;
    private ?int $lessonId = null;
    private ?int $attemptNumber = null;
    private ?Lesson $lesson = null;
    private ?TestSkel $skel = null;
    private string $testJson = "";

    protected function markup(): Component|array|null
    {
        return isset($this->lesson, $this->skel) ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text('Responder questionário')),
            component(Label::class, labelBold: true, label: 'Curso', children: text($this->lesson->course->name->unwrapOr('***'))),
            component(Label::class, labelBold: true, label: 'Aula', children: text($this->lesson->title->unwrapOr('***'))),
            component(Label::class, labelBold: true, label: 'Nº da aula', children: text($this->lesson->index->unwrapOr('***'))),
            component(Label::class, labelBold: true, label: 'Tentativa nº', children: text(($this->attemptNumber + 1) . " de " . TestCompleted::MAX_NUMBER_OF_ATTEMPTS )),

            tag('div', class: 'whitespace-pre-line mb-4 ml-2', children: text($this->skel->presentation_text->unwrapOr("***"))),

            tag('div', class: 'ml-2 font-bold mb-2', children: text("Você precisa de pelo menos {$this->skel->min_percent_for_approval->unwrapOr("***")}% de acerto neste questionário para ser aprovado")),

            tag('fill-lesson-test',

                id: $this->skel?->id->unwrapOr('') ?? '',
                lesson_id: $this->lesson?->id->unwrapOr('') ?? '',
                student_id: $_SESSION['user_id'] ?? 0,
                subscription_id: $this->subscriptionId,

                children:
                [
                    tag('span', name: "test-data-json", children: text($this->testJson))
                ]
            )
        ]) 
        :
        null;
    }
}