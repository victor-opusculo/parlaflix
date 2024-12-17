<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Courses;

use DateTimeZone;
use VictorOpusculo\Parlaflix\Components\Data\BasicSearchInput;
use VictorOpusculo\Parlaflix\Components\Data\DataGrid;
use VictorOpusculo\Parlaflix\Components\Data\OrderByLinks;
use VictorOpusculo\Parlaflix\Components\Data\Paginator;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class Home extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Cursos";

        $conn = Connection::get();
        try
        {

            $this->courseCount = (new Course)->getCount($conn, $_GET['q'] ?? '', true, null, true);
            $this->courses = (new Course)->getMultiple($conn, $_GET['q'] ?? '', $_GET['order_by'] ?? '', $_GET['page_num'] ?? 1, self::NUM_ITEMS_ON_PAGE, true, null, true);
            $this->courses = Data::transformDataRows($this->courses, 
            [
                'ID' => fn($m) => $m->id->unwrapOr(''),
                'Nome' => fn($m) => $m->name->unwrapOr('(Sem nome)'),
                'Carga horária' => fn($m) => $m->hours->unwrapOr(''),
                'Cadastrado em' => fn($c) => !empty($c->created_at->unwrapOr('')) ? 
                    date_create($c->created_at->unwrapOr('now'), new DateTimeZone('UTC'))
                    ->setTimezone(new DateTimeZone($_SESSION['user_timezone'] ?? 'America/Sao_Paulo'))
                    ->format('d/m/Y H:i:s')
                    :
                    ''
            ]);
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    private const NUM_ITEMS_ON_PAGE = 20;
    private array $courses = [];
    private int $courseCount = 0;

    protected function markup(): Component|array|null
    {
        return component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text('Cursos')),
            component(BasicSearchInput::class),
            component(OrderByLinks::class, linksDefinitions: [ 'ID' => 'id', 'Nome' => 'name', 'Horas' => 'hours', 'Data de cadastro' => 'created_at' ]),
            tag('div', class: 'my-2', children: tag('a', href: URLGenerator::generatePageUrl('/admin/panel/courses/create'), class: 'btn', children: text('Novo'))),
            component(DataGrid::class, 
                dataRows: $this->courses,
                detailsButtonURL: URLGenerator::generatePageUrl('/admin/panel/courses/{param}'),
                editButtonURL: URLGenerator::generatePageUrl('/admin/panel/courses/{param}/edit'),
                deleteButtonURL: URLGenerator::generatePageUrl('/admin/panel/courses/{param}/delete'),
                rudButtonsFunctionParamName: 'ID'
            ),
            component(Paginator::class, totalItems: $this->courseCount, numResultsOnPage: self::NUM_ITEMS_ON_PAGE, pageNum: $_GET['page_num'] ?? 1 )
        ]);
    }
}