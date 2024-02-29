<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Pages\PageId;

use Exception;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Pages\Page;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class Delete extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Excluir página";

        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->pageId))
                throw new \Exception("ID inválido!");

            $this->page = (new Page([ 'id' => $this->pageId ]))->getSingle($conn);
        }
        catch (Exception $e)
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
            tag('h1', children: text('Excluir página')),
            tag('delete-entity-form', 
                deletescripturl: URLGenerator::generateApiUrl("/administrator/panel/pages/{$this->page->id->unwrapOr(0)}"),
                gobacktourl: URLGenerator::generatePageUrl('/admin/panel/pages'),
                children:
                [
                    component(Label::class, label: 'ID', labelBold: true, children: text($this->page->id->unwrapOr(0))),
                    component(Label::class, label: 'Título', labelBold: true, children: text($this->page->title->unwrapOr(''))),
                    component(Label::class, label: 'Publicada?', labelBold: true, children: text($this->page->is_published->unwrapOr(false) ? 'Sim' : 'Não'))
                ]
            )
        ]) : null;
    }
}