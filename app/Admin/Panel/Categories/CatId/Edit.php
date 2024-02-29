<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Categories\CatId;

use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Category;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
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
        HeadManager::$title = "Editar categoria";

        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->categoryId))
                throw new \Exception("ID inválido!");

            $this->category = (new Category([ 'id' =>$this->categoryId ]))->getSingle($conn);
        }
        catch (\Exception $e)
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
            tag('h1', children: text('Editar categoria')),
            tag('edit-category-form', 
                id: $this->category->id->unwrapOr(0),
                title: $this->category->title->unwrapOr(''),
                icon_media_id: $this->category->icon_media_id->unwrapOr('')
            )
        ]) : 
        null;
    }
}