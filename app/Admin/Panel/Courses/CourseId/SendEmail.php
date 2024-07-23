<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Courses\CourseId;

use Exception;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Student;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Subscription;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class SendEmail extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Enviar e-mail para inscritos";

        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->courseId))
                throw new Exception("ID de curso inválido!");

            $this->course = (new Course([ 'id' => $this->courseId ]))->getSingle($conn);
            $subs = (new Subscription([ 'course_id' => $this->courseId ]))
                ->setCryptKey(Connection::getCryptoKey())
                ->getAllFromCourseForReport($conn, "", "name");

            $this->subscriptions = array_map(fn(Subscription $s) => [ 'name' => $s->getOtherProperties()->studentName, 'email' => $s->getOtherProperties()->studentEmail ], $subs);
        }
        catch (Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    protected $courseId;
    private ?Course $course = null;

    /** @var array<int,array{name:string,email:string}> */
    private array $subscriptions = [];

    protected function markup(): Component|array|null
    {
        return isset($this->course) ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text("Enviar e-mail para inscritos")),

            tag('send-subscribers-email',
                availableDestinationsJson: Data::hscq(json_encode($this->subscriptions)),
                back_to_url: "/admin/panel/courses/{$this->courseId}"
            )
        ])
        : null;
    }
}