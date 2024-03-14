<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Courses\CourseId;

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
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Subscription;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class ViewSubscriptions extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Visualizar inscrições de curso";
        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->courseId))
                throw new \Exception("ID inválido!");

            $this->course = (new Course([ 'id' => $this->courseId ]))->getSingle($conn);

            $getter = (new Subscription([ 'course_id' => $this->courseId ]))->setCryptKey(Connection::getCryptoKey());
            $this->subsCount = $getter->getCountFromCourse($conn, $_GET['q'] ?? '');
            $subscriptions = $getter->getMultipleFromCourse($conn, $_GET['q'] ?? '', $_GET['order_by'] ?? '', $_GET['page_num'] ?? 1, self::NUM_RESULTS_ON_PAGE);
            $this->subscriptions = Data::transformDataRows($subscriptions,
            [
                'ID' => fn($s) => $s->id->unwrapOr(0),
                'Estudante' => fn($s) => $s->getOtherProperties()->studentName ?? '',
                'E-mail' => fn($s) => $s->getOtherProperties()->studentEmail ?? '',
                'Aulas completadas' => fn($s) =>
                    (($s->getOtherProperties()->doneLessonCount ?? 0) . '/' . ($s->getOtherProperties()->lessonCount ?? 0)) . ' (' .
                    number_format(($s->getOtherProperties()->doneLessonCount ?? 0)/($s->getOtherProperties()->lessonCount ?? 1) * 100, 0) . '%)',
                'Data de inscrição' => fn($s) => 
                    date_create($s->datetime->unwrapOr('now'), new DateTimeZone('UTC'))->setTimezone(new DateTimeZone($_SESSION['user_timezone'] ?? 'America/Sao_Paulo'))->format('d/m/Y H:i:s')
            ]);
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    public const NUM_RESULTS_ON_PAGE = 30;
    protected $courseId;
    private ?Course $course = null;
    private array $subscriptions = [];
    private int $subsCount = 0;

    protected function markup(): Component|array|null
    {
        return isset($this->course) ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text("Inscrições de curso")),

            component(Label::class, labelBold: true, label: 'Curso', children:
                tag('a', class: 'link', href: URLGenerator::generatePageUrl("/admin/panel/courses/{$this->course->id->unwrapOr(0)}"), children: text($this->course->name->unwrapOr('')))
            ),

            component(BasicSearchInput::class),
            component(OrderByLinks::class, linksDefinitions: [ 'ID' => 'id', 'Nome de aluno' => 'name', 'E-mail' => 'email', 'Data de inscrição' => 'datetime' ]),

            tag('div', class: 'font-bold my-2', children:
            [
                text('Inscrições: ' . $this->subsCount)
            ]),
            component(DataGrid::class,
                dataRows: $this->subscriptions,
                detailsButtonURL: URLGenerator::generatePageUrl("/admin/panel/subscriptions/{param}"),
                rudButtonsFunctionParamName: 'ID'
            ),
            component(Paginator::class, totalItems: $this->subsCount, pageNum: $_GET['page_num'] ?? 1, numResultsOnPage: self::NUM_RESULTS_ON_PAGE),

            tag('div', class: 'mt-4 text-right', children:
                tag('a', class: 'btn', href: URLGenerator::generateApiUrl("administrator/panel/reports/export_course_subscriptions", [ 'course_id' => $this->courseId, 'q' => $_GET['q'] ?? '', 'order_by' => $_GET['order_by'] ?? '' ]), children: text("Exportar para CSV"))
            )
        ])
        : null;
    }
}