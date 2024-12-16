<?php
namespace VictorOpusculo\Parlaflix\App\Info\Category;

use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Components\Site\CategoryGrid;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Category;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class Home extends Component
{
    protected function setUp()
    {

        HeadManager::$title = "Cursos disponíveis";
        $conn = Connection::get();
        $firstCat = new Category([ 'title' => 'Todos os cursos' ]);
        $firstCat->coursesNumber = (new Course)->getCount($conn, '', false, null, true);
        $otherCats = (new Category)->getAllWithCourseCount($conn);

        foreach ($otherCats as $cat)
            $cat->fetchIcon($conn);

        $this->categories = 
        [
            $firstCat, ...$otherCats
        ];
    }

    private array $categories = [];

    protected function markup(): Component|array|null
    {
        return component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text("Cursos disponíveis")),
            component(CategoryGrid::class, categories: $this->categories)
        ]);
    }
}