<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Courses\CourseId\Surveys\SurveyId;

use Exception;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Components\Panels\DeleteEntityForm;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Survey;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
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
        HeadManager::$title = "Excluir opinião";
        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->surveyId))
                throw new Exception("ID inválido!");

            $this->survey = (new Survey([ 'id' => $this->surveyId ]))->setCryptKey(Connection::getCryptoKey())->getSingle($conn);
        }
        catch (Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    protected $courseId;
    protected $surveyId;
    private ?Survey $survey = null;

    protected function markup(): Component|array|null
    {
        return isset($this->survey) ? component(DefaultPageFrame::class, children:
        [
            tag('delete-entity-form', 
            deletescripturl: URLGenerator::generateApiUrl("/administrator/panel/surveys/{$this->surveyId}"),
            gobacktourl: "/admin/panel/courses/{$this->courseId}",
            children:
            [
                tag('h1', children: text('Excluir opinião')),
                component(Label::class, labelBold: true, label: "Curso", children: 
                    tag('a', class: 'link', href: URLGenerator::generatePageUrl("/admin/panel/courses/{$this->survey->course_id->unwrapOr(0)}"), children: text($this->survey->getOtherProperties()->courseName ?? '***')) 
                ),
                component(Label::class, labelBold: true, label: "Estudante", children: 
                    tag('a', class: 'link', href: URLGenerator::generatePageUrl("/admin/panel/students/{$this->survey->student_id->unwrapOr(0)}"), children: text($this->survey->getOtherProperties()->studentName ?? '***')) 
                ),
                component(Label::class, labelBold: true, label: "Nota dada", children: text($this->survey->points->unwrapOr(0) . " / 5")),
                component(Label::class, labelBold: true, label: "Mensagem", lineBreak: true, children:
                    tag('div', class: 'whitespace-pre-line', children: text($this->survey->message->unwrapOr("")))
                ),
            ])
        ])
        : null;
    }
}