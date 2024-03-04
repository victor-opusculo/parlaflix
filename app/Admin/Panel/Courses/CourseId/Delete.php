<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Courses\CourseId;

use VictorOpusculo\Parlaflix\Components\Data\DateTimeTranslator;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Components\Panels\DeleteEntityForm;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
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
        HeadManager::$title = "Excluir curso";

        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->courseId))
                throw new \Exception('ID inválido!');

            $this->course = (new Course([ 'id' => $this->courseId ]))
            ->getSingle($conn)
            ->fetchLessons($conn);
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    protected $courseId;
    private ?Course $course = null;

    protected function markup(): Component|array|null
    {
        return isset($this->course) ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text('Excluir curso')),

            tag('delete-entity-form', 
                deletescripturl: URLGenerator::generateApiUrl("/administrator/panel/courses/{$this->course->id->unwrapOr(0)}"),
                gobacktourl: URLGenerator::generatePageUrl('/admin/panel/courses'),
                children:
                [
                    component(Label::class, label: 'ID', labelBold: true, children: text($this->course->id->unwrapOr(0))),
                    component(Label::class, label: 'Nome', labelBold: true, children: text($this->course->name->unwrapOr(''))),
                    component(Label::class, label: 'Aulas', labelBold: true, children: text((string)count($this->course->lessons))),
                    component(Label::class, label: 'Visível?', labelBold: true, children: text($this->course->is_visible->unwrapOr(false) ? 'Sim' : 'Não')),
                    component(Label::class, label: 'Criado em', labelBold: true, children: 
                        component(DateTimeTranslator::class, utcDateTime: $this->course->created_at->unwrapOr(null))
                    ),
                ]
            )
        ])
        :
        null;
    }
}