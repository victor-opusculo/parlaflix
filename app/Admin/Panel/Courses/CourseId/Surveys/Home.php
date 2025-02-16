<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Courses\CourseId\Surveys;

use DateTime;
use DateTimeZone;
use VictorOpusculo\Parlaflix\Components\Data\BasicSearchInput;
use VictorOpusculo\Parlaflix\Components\Data\DataGrid;
use VictorOpusculo\Parlaflix\Components\Data\OrderByLinks;
use VictorOpusculo\Parlaflix\Components\Data\Paginator;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Survey;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class Home extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Opiniões";
     
        $courseId = (int)$this->courseId;

        $conn = Connection::get();
        $getter = new Survey();
        $getter->setCryptKey(Connection::getCryptoKey());
        $count = $getter->getCount($conn, $_GET['q'] ?? '', $courseId);
        $surveys = $getter->getMultiple($conn, $_GET['q'] ?? '', $_GET['order_by'] ?? 'created_at', $_GET['pageNum'] ?? 1, self::NUM_RESULTS_ON_PAGE, $courseId);

        $this->course = (new Course([ 'id' => $courseId ]))->getSingle($conn);
        $this->surveyCount = $count;
        $timeZone = new DateTimeZone($_SESSION['user_timezone']);
        $this->surveys = array_map(fn(Survey $s) =>
        [
            'ID' => $s->id->unwrapOr(0),
            'Estudante' => $s->getOtherProperties()->studentName ?? '***',
            'Nota dada' => (string)$s->points->unwrapOr(0) . ' / 5',
            'Mensagem' => Data::truncateText($s->message->unwrapOr(""), 80),
            'Data de envio (seu local)' => date_create($s->created_at->unwrapOr(""), new DateTimeZone("UTC"))
            ->setTimezone($timeZone)
            ->format("d/m/Y H:i:s")
        ], $surveys);
    }

    public const NUM_RESULTS_ON_PAGE = 20;
    protected $courseId;
    private ?Course $course = null;
    private array $surveys = [];
    private int $surveyCount = 0;

    protected function markup(): Component|array|null
    {
        return isset($this->course) ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text("Opiniões")),
            component(Label::class, labelBold: true, label: "Curso", children:
                tag('a', class: 'link', href: URLGenerator::generatePageUrl("/admin/panel/courses/{$this->courseId}"), children: text($this->course->name->unwrapOr("Indefinido")))
            ),
            component(BasicSearchInput::class),
            component(OrderByLinks::class, linksDefinitions:
            [
                'Pontos' => 'points',
                'Data de envio' => 'created_at'
            ]),
            component(DataGrid::class,
                dataRows: $this->surveys,
                detailsButtonURL: URLGenerator::generatePageUrl("/admin/panel/courses/{$this->courseId}/surveys/{param}"),
                deleteButtonURL: URLGenerator::generatePageUrl("/admin/panel/courses/{$this->courseId}/surveys/{param}/delete"),
                rudButtonsFunctionParamName: 'ID'
            ),
            component(Paginator::class,
                totalItems: $this->surveyCount,
                numResultsOnPage: self::NUM_RESULTS_ON_PAGE
            )

        ])
        : null;
    }
}