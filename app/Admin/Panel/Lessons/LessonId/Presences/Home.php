<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Lessons\LessonId\Presences;

use DateTimeZone;
use Exception;
use VictorOpusculo\Parlaflix\Components\Data\DataGrid;
use VictorOpusculo\Parlaflix\Components\Data\DataGridIcon;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Lesson;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\StudentLessonPassword;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Subscription;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class Home extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Presenças do curso";

        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->lessonId))
                throw new Exception("ID de aula inválido!");

            $lesson = (new Lesson([ 'id' => $this->lessonId ]))->getSingle($conn);
            $lesson
            ->setCryptKey(Connection::getCryptoKey())
            ->fetchCourse($conn)
            ->fetchPresences($conn);

            $lesson->course
            ->setCryptKey(Connection::getCryptoKey())
            ->fetchSubscriptions($conn);

            $this->course = $lesson->course;
            $this->lesson = $lesson;

            $this->presenceTableRows = array_map(fn(StudentLessonPassword $sp) => 
            [
                'id' => $sp->id->unwrapOr(0),
                'Estudante' => $sp->getOtherProperties()->studentName ?? "***",
                'E-mail' => $sp->getOtherProperties()->studentEmail ?? "***",
                'Senha informada' => $sp->given_password->unwrapOr(""),
                'Está correta?' => $sp->is_correct->unwrapOr(false) ? new DataGridIcon("assets/pics/check.png", "Sim") : new DataGridIcon("assets/pics/wrong.png", "Não")
            ], $lesson->studentPresences);

            $this->availableSubscriptions = array_map(fn(Subscription $sub) =>
            [
                'id' => $sub->student_id->unwrapOr(0),
                'name' => $sub->getOtherProperties()->studentName ?? "***",
                'email' => $sub->getOtherProperties()->studentEmail ?? "***"
            ], $this->course->subscriptions);
        }
        catch (Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    protected $lessonId;

    private ?Course $course;
    private ?Lesson $lesson;
    private array $presenceTableRows = [];

    private array $availableSubscriptions = [];

    protected function markup(): Component|array|null
    {
        return isset($this->course) ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text(sprintf("Presenças: %s", Data::truncateText($this->course->name->unwrapOr("***"), 80)))),
            tag('h2', children: text(sprintf("Aula %d: %s", $this->lesson->index->unwrapOr(0) + 1, $this->lesson->title->unwrapOr("***")))),

            component(DataGrid::class, 
                dataRows: $this->presenceTableRows,
                deleteButtonURL: URLGenerator::generatePageUrl("/admin/panel/presences/{param}/delete"),
                columnsToHide: [ 'id' ]
            ),

            tag('mark-student-presence', 
                available_subscriptions: Data::hscq(json_encode($this->availableSubscriptions)),
                lesson_id: $this->lesson->id->unwrapOr(0)
            )

        ]) 
        : null;
    }
} 