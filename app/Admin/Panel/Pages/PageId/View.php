<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Pages\PageId;

use Exception;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Components\Panels\ConvenienceLinks;
use VictorOpusculo\Parlaflix\Components\Site\PageViewer;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Pages\Page;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\scTag;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class View extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Visualizar página";

        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->pageId))
                throw new Exception("ID inválido!");

            $this->page = (new Page([ 'id' => $this->pageId ]))->getSingle($conn); 
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    protected $pageId;
    private ?Page $page = null;

    protected function markup(): Component|array|null
    {
        return $this->page ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text('Visualizar página')),
            component(PageViewer::class, page: $this->page),
            scTag('hr'),
            component(Label::class, label: 'ID', labelBold: true, children: text($this->page->id->unwrapOr(0))),
            component(Label::class, label: 'Link de acesso', labelBold: true, children:
                tag('a', 
                    class: 'link', 
                    href: URLGenerator::generatePageUrl("/page/{$this->page->id->unwrapOr(0)}"), 
                    children: text(URLGenerator::generatePageUrl("/page/{$this->page->id->unwrapOr(0)}"))
                )
            ),
            component(ConvenienceLinks::class,
                editUrl: URLGenerator::generatePageUrl("/admin/panel/pages/{$this->page->id->unwrapOr(0)}/edit"),
                deleteUrl: URLGenerator::generatePageUrl("/admin/panel/pages/{$this->page->id->unwrapOr(0)}/delete")
            )
        ]) : null;
    }
}