<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Media;

use VictorOpusculo\Parlaflix\Components\Data\BasicSearchInput;
use VictorOpusculo\Parlaflix\Components\Data\DataGrid;
use VictorOpusculo\Parlaflix\Components\Data\OrderByLinks;
use VictorOpusculo\Parlaflix\Components\Data\Paginator;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Media\Media;
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
        HeadManager::$title = "Mídias";

        $conn = Connection::get();
        try
        {
            $this->mediaCount = (new Media)->getCount($conn, $_GET['q'] ?? '');
            $this->medias = (new Media)->getMultiple($conn, $_GET['q'] ?? '', $_GET['order_by'] ?? '', $_GET['page_num'] ?? 1, self::NUM_ITEMS_ON_PAGE);
            $this->medias = Data::transformDataRows($this->medias, 
            [
                'ID' => fn($m) => $m->id->unwrapOr(''),
                'Nome' => fn($m) => $m->name->unwrapOr('(Sem nome)'),
                'Extensão' => fn($m) => $m->file_extension->unwrapOr('-')
            ]);
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    private const NUM_ITEMS_ON_PAGE = 20;
    private array $medias = [];
    private int $mediaCount = 0;

    protected function markup(): Component|array|null
    {
        return component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text('Mídias')),
            component(BasicSearchInput::class),
            component(OrderByLinks::class, linksDefinitions: [ 'ID' => 'id', 'Nome' => 'name', 'Extensão' => 'file_extension' ]),
            tag('div', class: 'my-2', children: tag('a', href: URLGenerator::generatePageUrl('/admin/panel/media/create'), class: 'btn', children: text('Nova'))),
            component(DataGrid::class, 
                dataRows: $this->medias,
                detailsButtonURL: URLGenerator::generatePageUrl('/admin/panel/media/{param}'),
                editButtonURL: URLGenerator::generatePageUrl('/admin/panel/media/{param}/edit'),
                deleteButtonURL: URLGenerator::generatePageUrl('/admin/panel/media/{param}/delete'),
                rudButtonsFunctionParamName: 'ID'
            ),
            component(Paginator::class, totalItems: $this->mediaCount, numResultsOnPage: self::NUM_ITEMS_ON_PAGE, pageNum: $_GET['page_num'] ?? 1 )
        ]);
    }
}