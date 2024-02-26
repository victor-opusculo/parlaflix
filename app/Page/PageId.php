<?php
namespace VictorOpusculo\Parlaflix\App\Page;

use Exception;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Components\Site\PageViewer;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Pages\Page;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;

final class PageId extends Component
{
    protected function setUp()
    {
        $conn = Connection::get();
        HeadManager::$title = "Página";
        try
        {
            if (!Connection::isId($this->pageId))
                throw new Exception("ID inválido!");

            $this->page = (new Page([ 'id' => $this->pageId ]))->getSingle($conn);

            if (!$this->page->is_published->unwrapOr(false))
            {
                $this->page = null;
                throw new \Exception("Página não existente!");
            }

            HeadManager::$title = $this->page->title->unwrapOr('Página');
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    protected $pageId;
    private ?Page $page;

    protected function markup(): Component|array|null
    {
        return $this->page ? component(DefaultPageFrame::class, children: component(PageViewer::class, page: $this->page)) : null;
    }
}