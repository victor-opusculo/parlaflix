<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Subscriptions\SubscriptionId;

use VictorOpusculo\Parlaflix\Components\Data\DataGrid;
use VictorOpusculo\Parlaflix\Components\Data\DateTimeTranslator;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Components\Panels\ConvenienceLinks;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\LessonTests;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Subscription;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestCompleted;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class View extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Visualizar inscrição";
        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->subscriptionId))
                throw new \Exception("ID inválido!");

            $this->subscription = (new Subscription([ 'id' => $this->subscriptionId ]))
            ->setCryptKey(Connection::getCryptoKey())
            ->getSingleWithProgressData($conn)
            ->fetchCourse($conn);

            $testsCompleted = new TestCompleted([ 'subscription_id' => $this->subscriptionId ])->getAllFromSubscription($conn);
            array_walk($testsCompleted, fn(TestCompleted $t) => $t->fetchLesson($conn));

            $this->testsCompleted = array_map(fn(TestCompleted $test) =>
            [
                'ID' => $test->id->unwrapOr("***"),
                'Aula' => $test->lesson->title->unwrapOr("***"),
                'Nota' => number_format(LessonTests::calculateGrade(LessonTests::calculateCorrectAnswers($test->buildStructure())), 1, ",", ".") . "%",
                'Resultado' => $test->is_approved->unwrapOr("***") ? "Aprovado" : "Reprovado"
            ], $testsCompleted);

        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    protected $subscriptionId;
    private array $testsCompleted = [];
    private ?Subscription $subscription = null;

    protected function markup(): Component|array|null
    {
        return component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text("Visualizar inscrição")),

            component(Label::class, labelBold: true, label: 'ID', children: text($this->subscription->id->unwrapOr(0))),
            component(Label::class, labelBold: true, label: 'Curso', children: 
                tag('a', class: 'link', href: URLGenerator::generatePageUrl("/admin/panel/courses/{$this->subscription->course->id->unwrapOr(0)}"), children: text($this->subscription->course->name->unwrapOr('')))
            ),
            component(Label::class, labelBold: true, label: 'Estudante', children: 
                tag('a', class: 'link', href: URLGenerator::generatePageUrl("/admin/panel/students/{$this->subscription->student_id->unwrapOr(0)}"), children: text($this->subscription->getOtherProperties()->studentName ?? ''))
            ),
            component(Label::class, labelBold: true, label: 'Data de inscrição', children: component(DateTimeTranslator::class, utcDateTime: $this->subscription->datetime->unwrapOr(''))),

            component(Label::class, labelBold: true, label: 'Aulas completadas', children: 
                text(
                    ($this->subscription->getOtherProperties()->doneLessonCount ?? 0) . '/' . ($this->subscription->getOtherProperties()->lessonCount ?? 1) 
                    . ' (' . number_format(($this->subscription->getOtherProperties()->doneLessonCount ?? 0) / ($this->subscription->getOtherProperties()->lessonCount ?? 1) * 100, 2, ',') . '%)'
                    )
            ),
            component(Label::class, labelBold: true, label: 'Pontos acumulados', children: text($this->subscription->getOtherProperties()->studentPoints ?? 0)),
            component(Label::class, labelBold: true, label: 'Pontos requeridos para aprovação', children: text($this->subscription->course->min_points_required->unwrapOr(0))),
            component(Label::class, labelBold: true, label: 'Máximo de pontos possível', children: text($this->subscription->getOtherProperties()->maxPoints ?? 0)),

            component(Label::class, labelBold: true, label: 'Certificado', children: 
                (($this->subscription->getOtherProperties()->studentPoints ?? 0) >= $this->subscription->course->min_points_required->unwrapOr(INF))
                ? tag('a', class: 'btn', href: URLGenerator::generateScriptUrl('certificate/generate_admin.php', [ 'subscription_id' => $this->subscription->id->unwrapOr(0) ]), children: text('Gerar')  )
                : text('Não disponível')  
            ),

            tag('fieldset', class: 'fieldset', children:
            [
                tag('legend', children: text("Respostas de questionários")),
                component(DataGrid::class, 
                    dataRows: $this->testsCompleted, 
                    rudButtonsFunctionParamName: 'ID',
                    detailsButtonURL: URLGenerator::generatePageUrl("/admin/panel/tests_completed/{param}"),
                    deleteButtonURL: URLGenerator::generatePageUrl("/admin/panel/tests_completed/{param}/delete")
                )
            ]),

            component(ConvenienceLinks::class, deleteUrl: URLGenerator::generatePageUrl("/admin/panel/subscriptions/{$this->subscription->id->unwrapOr(0)}/delete"))

        ]);
    }
}