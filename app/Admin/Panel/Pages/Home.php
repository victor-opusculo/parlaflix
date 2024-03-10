<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Pages;

use VictorOpusculo\Parlaflix\Components\Data\BasicSearchInput;
use VictorOpusculo\Parlaflix\Components\Data\DataGrid;
use VictorOpusculo\Parlaflix\Components\Data\DataGridIcon;
use VictorOpusculo\Parlaflix\Components\Data\OrderByLinks;
use VictorOpusculo\Parlaflix\Components\Data\Paginator;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Pages\Page;
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
        HeadManager::$title = "Páginas";

        $conn = Connection::get();
        try
        {
            $this->pageCount = (new Page)->getCount($conn, $_GET['q'] ?? '');
            $this->pages = (new Page)->getMultiple($conn, $_GET['q'] ?? '', $_GET['order_by'] ?? '', $_GET['page_num'] ?? 1, self::NUM_ITEMS_ON_PAGE);
            $this->pages = Data::transformDataRows($this->pages, 
            [
                'ID' => fn($m) => $m->id->unwrapOr(''),
                'Título' => fn($m) => $m->title->unwrapOr('(Sem nome)'),
                'Publicada?' => fn($m) => $m->is_published->unwrapOr(0) ? new DataGridIcon('assets/pics/check.png', 'Sim', 'Sim') : new DataGridIcon('assets/pics/wrong.png', 'Não', 'Não')
            ]);
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    private const NUM_ITEMS_ON_PAGE = 20;
    private array $pages = [];
    private int $pageCount = 0;

    protected function markup(): Component|array|null
    {
        return component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text('Páginas')),
            component(BasicSearchInput::class),
            component(OrderByLinks::class, linksDefinitions: [ 'ID' => 'id', 'Título' => 'title', 'Publicada' => 'is_published' ]),
            tag('div', class: 'my-2', children:
            [
                tag('a', href: URLGenerator::generatePageUrl('/admin/panel/pages/create'), class: 'btn mr-2', children: text('Nova')),
                tag('a', href: URLGenerator::generatePageUrl('/admin/panel/pages/set_homepage'), class: 'btn', children: text('Definir página inicial'))
            ]),
            component(DataGrid::class, 
                dataRows: $this->pages,
                detailsButtonURL: URLGenerator::generatePageUrl('/admin/panel/pages/{param}'),
                editButtonURL: URLGenerator::generatePageUrl('/admin/panel/pages/{param}/edit'),
                deleteButtonURL: URLGenerator::generatePageUrl('/admin/panel/pages/{param}/delete'),
                rudButtonsFunctionParamName: 'ID'
            ),
            component(Paginator::class, totalItems: $this->pageCount, numResultsOnPage: self::NUM_ITEMS_ON_PAGE, pageNum: $_GET['page_num'] ?? 1 )
        ]);
    }
}