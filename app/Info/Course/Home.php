<?php
namespace VictorOpusculo\Parlaflix\App\Info\Course;

use VictorOpusculo\Parlaflix\Components\Data\BasicSearchInput;
use VictorOpusculo\Parlaflix\Components\Data\OrderByLinks;
use VictorOpusculo\Parlaflix\Components\Data\Paginator;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Components\Site\CourseGrid;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
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
        HeadManager::$title = "Cursos disponíveis";
        $conn = Connection::get();
        try
        {
            $getter = new Course();
            $this->courseCount = $getter->getCount($conn, $_GET['q'] ?? '', false);
            $this->courses = $getter->getMultiple($conn, $_GET['q'] ?? '', $_GET['order_by'] ?? 'name', $_GET['page_num'] ?? 1, self::NUM_RESULTS_ON_PAGE, false);
            
            foreach ($this->courses as $c)
                $c->fetchCoverMedia($conn);
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    public const NUM_RESULTS_ON_PAGE = 15;

    private array $courses = [];
    private int $courseCount = 0;

    protected function markup(): Component|array|null
    {
        return component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text('Cursos')),
            component(BasicSearchInput::class),
            component(OrderByLinks::class, linksDefinitions:
            [
                'Nome' => 'name',
                'Carga horária' => 'hours'
            ]),
            component(CourseGrid::class, courses: $this->courses),
            component(Paginator::class, totalItems: $this->courseCount, pageNum: $_GET['page_num'] ?? 1, numResultsOnPage: self::NUM_RESULTS_ON_PAGE)
        ]);
    }
}