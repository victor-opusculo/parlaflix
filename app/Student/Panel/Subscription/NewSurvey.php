<?php
namespace VictorOpusculo\Parlaflix\App\Student\Panel\Subscription;

use Exception;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Survey;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Subscription;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\scTag;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class NewSurvey extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Enviar opinião";

        $conn = Connection::get();
        try
        {
            $subscriptionId = $_GET['subscription_id'] ?? 0;
            $studentId = $_SESSION['user_id'] ?? 0;

            $subscription = (new Subscription([ 'id' => $subscriptionId, 'student_id' => $_SESSION['user_id'] ?? 0 ]))
            ->getSingleFromStudent($conn)
            ->fetchCourse($conn);

            $courseId = $subscription->course_id->unwrapOr(0);

            $isApproved = ($subscription->getOtherProperties()->studentPoints ?? 0) >= ($subscription->course->min_points_required->unwrapOr(INF));

            if (!$isApproved)
                throw new Exception("Você não foi aprovado(a) neste curso!");

            $getter = new Survey([ 'course_id' => $courseId, 'student_id' => $studentId ]);

            if ($getter->existsFromStudentAndCourse($conn))
                throw new Exception("Você já enviou sua opinião para este curso!");

            $this->subscriptionId = $subscriptionId;
            $this->canSendNewSurvey = true;
            $this->subscription = $subscription;
        }
        catch (Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
            $this->canSendNewSurvey = false;
        }
    }

    private bool $canSendNewSurvey = true;
    private int $subscriptionId;
    private ?Subscription $subscription = null;

    protected function markup(): Component|array|null
    {
        return $this->canSendNewSurvey && $this->subscription
            ? component(DefaultPageFrame::class, children:
            [
                tag('h1', children: text("Enviar opinião")),
                component(Label::class, labelBold: true, label: "Curso", children: text($this->subscription->course->name->unwrapOr("***"))),
                scTag('hr'),
                tag('student-survey-form', subscription_id: $this->subscriptionId)
            ])
            : null;
    }
}