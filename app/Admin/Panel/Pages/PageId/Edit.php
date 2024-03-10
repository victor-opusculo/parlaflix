<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Pages\PageId;

use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Pages\Page;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class Edit extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Editar página";
        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->pageId))
                throw new \Exception("ID inválido!");

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
            tag('h1', children: text('Editar página')),
            tag('edit-page-form',
                id: $this->page->id->unwrapOr(0),
                title: Data::hscq($this->page->title->unwrapOr('')),
                content: Data::hscq($this->page->content->unwrapOr('')),
                html_enabled: $this->page->html_enabled->unwrapOr(0),
                is_published: $this->page->is_published->unwrapOr(0)    
            )
        ]) : null;
    }
}