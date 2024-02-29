<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Categories;

use VictorOpusculo\Parlaflix\Components\Data\BasicSearchInput;
use VictorOpusculo\Parlaflix\Components\Data\DataGrid;
use VictorOpusculo\Parlaflix\Components\Data\DataGridIcon;
use VictorOpusculo\Parlaflix\Components\Data\OrderByLinks;
use VictorOpusculo\Parlaflix\Components\Data\Paginator;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Category;
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
        HeadManager::$title = "Categorias de cursos";

        $conn = Connection::get();
        try
        {
            $this->catCount = (new Category)->getCount($conn, $_GET['q'] ?? '');
            $this->categories = (new Category)->getMultiple($conn, $_GET['q'] ?? '', $_GET['order_by'] ?? '', $_GET['page_num'] ?? 1, self::NUM_ITEMS_ON_PAGE);
            
            foreach ($this->categories as $cat)
                $cat->fetchIcon($conn);
            
            $this->categories = Data::transformDataRows($this->categories, 
            [
                'ID' => fn($m) => $m->id->unwrapOr(''),
                'Título' => fn($m) => $m->title->unwrapOr('(Sem nome)'),
                'Ícone' => fn($m) => $m->icon_media_id->unwrapOr(0) ? new DataGridIcon($m->icon->fileNameFromBaseDir(), 'Ícone de categoria', null, 64, 64) : "Não há"
            ]);
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    private const NUM_ITEMS_ON_PAGE = 20;
    private array $categories = [];
    private int $catCount = 0;

    protected function markup(): Component|array|null
    {
        return component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text('Categorias de cursos')),
            component(BasicSearchInput::class),
            component(OrderByLinks::class, linksDefinitions: [ 'ID' => 'id', 'Título' => 'title' ]),
            tag('div', class: 'my-2', children: tag('a', href: URLGenerator::generatePageUrl('/admin/panel/categories/create'), class: 'btn', children: text('Nova'))),
            component(DataGrid::class, 
                dataRows: $this->categories,
                //detailsButtonURL: URLGenerator::generatePageUrl('/admin/panel/categories/{param}'),
                editButtonURL: URLGenerator::generatePageUrl('/admin/panel/categories/{param}/edit'),
                deleteButtonURL: URLGenerator::generatePageUrl('/admin/panel/categories/{param}/delete'),
                rudButtonsFunctionParamName: 'ID'
            ),
            component(Paginator::class, totalItems: $this->catCount, numResultsOnPage: self::NUM_ITEMS_ON_PAGE, pageNum: $_GET['page_num'] ?? 1 )
        ]);
    }
}