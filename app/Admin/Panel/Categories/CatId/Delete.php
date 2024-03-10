<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Categories\CatId;

use Exception;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Category;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
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
        HeadManager::$title = "Excluir categoria";

        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->categoryId))
                throw new \Exception("ID inválido!");

            $this->category = (new Category([ 'id' => $this->categoryId ]))->getSingle($conn);
        }
        catch (Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    protected $categoryId;
    private ?Category $category = null;

    protected function markup(): Component|array|null
    {
        return isset($this->category) ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text('Excluir categoria')),
            tag('delete-entity-form', 
                deletescripturl: URLGenerator::generateApiUrl("/administrator/panel/categories/{$this->category->id->unwrapOr(0)}"),
                gobacktourl: '/admin/panel/categories',
                children:
                [
                    component(Label::class, label: 'ID', labelBold: true, children: text($this->category->id->unwrapOr(0))),
                    component(Label::class, label: 'Título', labelBold: true, children: text($this->category->title->unwrapOr('')))
                ]
            )
        ]) : null;
    }
}