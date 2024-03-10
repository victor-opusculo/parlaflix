<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Courses\CourseId;

use DateTime;
use DateTimeZone;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Components\Panels\ConvenienceLinks;
use VictorOpusculo\Parlaflix\Components\Panels\LessonViewer;
use VictorOpusculo\Parlaflix\Components\Site\ImageMediaViewer;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\rawText;
use function VictorOpusculo\PComp\Prelude\scTag;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class View extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Visualizar curso";
        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->courseId))
                throw new \Exception('ID inválido!');

            $this->course = (new Course([ 'id' => $this->courseId ]))
            ->getSingle($conn)
            ->informDateTimeZone($_SESSION['user_timezone'] ?? 'America/Sao_Paulo')
            ->fetchLessons($conn)
            ->fetchCategoriesJoints($conn)
            ->fetchCoverMedia($conn);
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
            tag('h1', children: text('Visualizar curso')),

            component(Label::class, label: 'Visível?', labelBold: true, children: text($this->course->is_visible->unwrapOr(0) ? 'Sim' : 'Não')),
            component(Label::class, label: 'Cadastrado em', labelBold: true, children: 
                text($this->course->created_at->unwrapOr(false) ? 
                    date_create($this->course->created_at->unwrap(), new DateTimeZone('UTC'))
                    ->setTimezone(new DateTimeZone($_SESSION['user_timezone'] ?? 'America/Sao_Paulo'))
                    ->format('d/m/Y H:i:s')
                    : 
                    '***'
                )
            ),
            component(Label::class, label: 'ID', labelBold: true, children: text($this->course->id->unwrapOr(0))),
            component(Label::class, label: 'Nome', labelBold: true, children: text($this->course->name->unwrapOr(''))),
            component(Label::class, label: 'Mais informações', labelBold: true, lineBreak: true, children: rawText(nl2br(Data::hsc($this->course->presentation_html->unwrapOr(0))))),
            component(Label::class, label: 'Imagem de capa', labelBold: true, lineBreak: true, children:
                component(ImageMediaViewer::class, media: $this->course->coverMedia ?? null, forceWidth: 256)
            ),
            component(Label::class, label: 'Carga horária', labelBold: true, children: text($this->course->hours->unwrapOr(0))),
            component(Label::class, label: 'Texto para o certificado', labelBold: true, lineBreak: true, children:
                rawText(nl2br(Data::hsc($this->course->certificate_text->unwrapOr(''))))    
            ),
            component(Label::class, label: 'Pontuação mínima requerida para aprovação', labelBold: true, children: text($this->course->min_points_required->unwrapOr('Indefinido'))),

            component(Label::class, label: 'Categorias', labelBold: true, children: text(array_reduce($this->course->categoriesJoints, fn($carry, $cj) => ($carry ? ', ' : '') . $cj->getOtherProperties()->title ?? '', null))),

            tag('h2', children: text('Aulas')),

            ...array_map(fn($less) => component(LessonViewer::class, lesson: $less), $this->course->lessons),

            component(ConvenienceLinks::class, 
                editUrl: URLGenerator::generatePageUrl("/admin/panel/courses/{$this->courseId}/edit"),
                deleteUrl: URLGenerator::generatePageUrl("/admin/panel/courses/{$this->courseId}/delete")
            )
        ]):
        null;
    }
}