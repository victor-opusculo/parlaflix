<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Courses\CourseId\Surveys\SurveyId;

use Exception;
use VictorOpusculo\Parlaflix\Components\Data\DateTimeTranslator;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Components\Panels\ConvenienceLinks;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Survey;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
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
        HeadManager::$title = "Ver opinião";
        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->surveyId))
                throw new Exception("ID inválido!");

            $getter = new Survey([ 'id' => $this->surveyId ]);
            $getter->setCryptKey(Connection::getCryptoKey());

            $this->survey = $getter->getSingle($conn);
        }
        catch (Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    protected $surveyId;
    protected $courseId;
    private ?Survey $survey = null;

    protected function markup(): Component|array|null
    {
        return isset($this->survey) ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text("Ver opinião")),
            component(Label::class, labelBold: true, label: "ID", children: text($this->survey->id->unwrapOr(0))),
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

            component(Label::class, labelBold: true, label: "Enviada em", lineBreak: true, children: component(DateTimeTranslator::class, utcDateTime: $this->survey->created_at->unwrapOr("") ) ),


            component(ConvenienceLinks::class, deleteUrl: URLGenerator::generatePageUrl("/admin/panel/courses/{$this->courseId}/surveys/{$this->surveyId}/delete"))

        ])
        : null;
    }
}