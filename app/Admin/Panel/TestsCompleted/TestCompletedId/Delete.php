<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\TestsCompleted\TestcompletedId;

use Exception;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\LessonTests;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestCompleted;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class Delete extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Excluir questionário respondido";
        $conn = Connection::get();
        try
        {

            if (!Connection::isId($this->testCompletedId))
                throw new Exception("ID inválido!");

            $this->test = new TestCompleted([ 'id' => $this->testCompletedId ])
            ->getSingle($conn)
            ->fetchSkel($conn)
            ->fetchSubscription($conn)
            ->fetchLesson($conn);

            $this->test->subscription
            ->setCryptKey(Connection::getCryptoKey())
            ->fetchCourse($conn)
            ->fetchStudent($conn);
        }
        catch (Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }
    
    protected $testCompletedId;
    private ?TestCompleted $test = null;

    protected function markup(): Component|array|null
    {
        return isset($this->test) ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text("Excluir questionário respondido")),

            tag('delete-entity-form-rpc', 
                functionsurl: URLGenerator::generateFunctionUrl("/admin/panel/tests_completed/{$this->testCompletedId}"), 
                deletefnname: 'deleteTest',
                gobacktourl: "/admin/panel/subscriptions/{$this->test->subscription->id->unwrapOr(0)}",
                children:
                [
                    tag('p', class: 'font-bold text-center mb-4', children: text("Tem certeza de que deseja excluir este questionário respondido? Esta operação é irreversível!")),

                    component(Label::class, labelBold: true, label: "ID", children: text($this->test->id->unwrapOr("***"))),
                    component(Label::class, labelBold: true, label: "Estudante", children: text($this->test->subscription->student->full_name->unwrapOr("***"))),
                    component(Label::class, labelBold: true, label: "Curso", children: text($this->test->subscription->course->name->unwrapOr("***"))),
                    component(Label::class, labelBold: true, label: "Aula", children: text($this->test->lesson->title->unwrapOr("***"))),
                    component(Label::class, labelBold: true, label: "Nota", children: text(
                            number_format(
                                LessonTests::calculateGrade(
                                    LessonTests::calculateCorrectAnswers($this->test->buildStructure())
                                ),
                                1, ',', '.'
                            ) . "%"
                        ),
                    ),
                    component(Label::class, labelBold: true, label: "Mínimo requerido", children: text($this->test->skel->min_percent_for_approval->unwrapOr("***") . "%")),
                ]
            )
        ])
        : null;
    }
}