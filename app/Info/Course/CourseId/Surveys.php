<?php
namespace VictorOpusculo\Parlaflix\App\Info\Course\CourseId;

use Exception;
use VictorOpusculo\Parlaflix\Components\Data\Paginator;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Survey;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\scTag;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class Surveys extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Avaliações de curso";
        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->courseId))
                throw new Exception("ID inválido!");

            $getter = new Survey([ 'course_id' => $this->courseId ]);
            $getter->setCryptKey(Connection::getCryptoKey());
            $this->surveyCount = $getter->getCount($conn, "", $this->courseId);
            $this->surveys = $getter->getMultiple($conn, "", "created_at", $_GET['pageNum'] ?? 1, self::NUM_RESULTS_ON_PAGE, $this->courseId);
            $this->course = (new Course([ 'id' => $this->courseId ]))->getSingle($conn);

            HeadManager::$title = "Avaliações: {$this->course->name->unwrapOr('***')}";
        }
        catch (Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }

    }

    public const NUM_RESULTS_ON_PAGE = 10;

    protected $courseId;
    /** @var array<Survey> */
    private array $surveys = [];
    private int $surveyCount = 0;
    private ?Course $course = null;

    protected function markup(): Component|array|null
    {
        return isset($this->course) ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text("Avaliações")),
            tag('h3', class: 'text-center', children: text($this->course->name->unwrapOr("***"))),
            count($this->surveys) > 0
            ? array_map(fn(Survey $s) => 
                [
                    component(Label::class, labelBold: true, label: "De", children: text(Data::firstName($s->getOtherProperties()->studentName ?? "***"))),
                    component(Label::class, labelBold: true, label: "Nota", children:
                        tag('div', class: 'stars5Mask w-[200px] h-[42px] inline-block mr-4', children:
                            tag('progress', class: 'w-full h-full starProgressBar', min: 0, max: 5, value: $s->points->unwrapOr(0))
                        ),
                    ),
                    tag('blockquote', cite: Data::hscq(Data::firstName($s->getOtherProperties()->studentName ?? "***")), children:
                        tag('div', class: 'ml-2 whitespace-pre-line italic mb-4', children: text($s->message->unwrapOr(""))),
                    ),
                    scTag("hr")
                ], $this->surveys)
            : tag('p', class: 'text-center', children: text("Este curso ainda não tem avaliações!")),
            component(Paginator::class, totalItems: $this->surveyCount, numResultsOnPage: self::NUM_RESULTS_ON_PAGE)
        ])
        : null;
    }
}