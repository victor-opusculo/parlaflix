<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Courses;

use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Category;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class Create extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Novo curso";
        $conn = Connection::get();
        $this->categories = (new Category)->getAll($conn);
    }

    private array $categories = [];

    protected function markup(): Component|array|null
    {
        return component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text('Novo curso')),
            tag('edit-course-form', categories_available_json: Data::hscq(json_encode($this->categories)))
        ]);
    }
}