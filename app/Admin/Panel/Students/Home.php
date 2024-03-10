<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Students;

use VictorOpusculo\Parlaflix\Components\Data\BasicSearchInput;
use VictorOpusculo\Parlaflix\Components\Data\DataGrid;
use VictorOpusculo\Parlaflix\Components\Data\OrderByLinks;
use VictorOpusculo\Parlaflix\Components\Data\Paginator;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Student;
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
        HeadManager::$title = "Estudantes cadastrados";
        $conn = Connection::get();
        try
        {
            $getter = (new Student)->setCryptKey(Connection::getCryptoKey());
            $this->studentsCount = $getter->getCount($conn, $_GET['q'] ?? '');
            $students = $getter->getMultiple($conn, $_GET['q'] ?? '', $_GET['order_by'] ?? '', $_GET['page_num'] ?? 1, self::NUM_RESULTS_ON_PAGE);
            $this->students = Data::transformDataRows($students, 
            [
                'ID' => fn($s) => $s->id->unwrapOr(0),
                'Nome completo' => fn($s) => $s->full_name->unwrapOr(''),
                'E-mail' => fn($s) => $s->email->unwrapOr(''),
                'Telefone' => fn($s) => $s->other_data->unwrap()->telephone->unwrapOr('')
            ]);
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    public const NUM_RESULTS_ON_PAGE = 20;
    private array $students = [];
    private int $studentsCount = 0;

    protected function markup(): Component|array|null
    {
        return component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text('Estudantes cadastrados')),

            component(BasicSearchInput::class),
            component(OrderByLinks::class, linksDefinitions: [ 'ID' => 'id', 'Nome' => 'name', 'E-mail' => 'email' ]),

            tag('div', class: 'my-4', children: 
                text($this->studentsCount > 1 ? "{$this->studentsCount} estudantes" : "{$this->studentsCount} estudante")
            ),

            component(DataGrid::class,
                dataRows: $this->students,
                rudButtonsFunctionParamName: 'ID',
                detailsButtonURL: URLGenerator::generatePageUrl('/admin/panel/students/{param}'),
                editButtonURL: URLGenerator::generatePageUrl('/admin/panel/students/{param}/edit'),
                deleteButtonURL: URLGenerator::generatePageUrl('/admin/panel/students/{param}/delete'),
            ),

            component(Paginator::class, totalItems: $this->studentsCount, pageNum: $_GET['page_num'] ?? 1, numResultsOnPage: self::NUM_RESULTS_ON_PAGE)
        ]);
    }
}