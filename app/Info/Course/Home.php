<?php
namespace VictorOpusculo\Parlaflix\App\Info\Course;

use VictorOpusculo\Parlaflix\Components\Data\BasicSearchInput;
use VictorOpusculo\Parlaflix\Components\Data\OrderByLinks;
use VictorOpusculo\Parlaflix\Components\Data\Paginator;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Components\Site\CourseGrid;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Category;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;
use VictorOpusculo\PComp\ScriptManager;

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
            session_name('parlaflix_student_user');
            session_start();

            $userIsMember = (bool)($_SESSION['user_is_member'] ?? false);
            $this->isUserAbelMember = $userIsMember;

            $getter = new Course();
            $this->courseCount = $getter->getCount($conn, $_GET['q'] ?? '', false, $_GET['category_id'] ?? null, true);
            $this->courses = $getter->getMultiple($conn, $_GET['q'] ?? '', $_GET['order_by'] ?? 'name', $_GET['page_num'] ?? 1, self::NUM_RESULTS_ON_PAGE, false, $_GET['category_id'] ?? null, true);
            
            foreach ($this->courses as $c)
                $c
                ->fetchCoverMedia($conn)
                ->fetchAverageSurveyPoints($conn);

            $this->categories = (new Category)->getAll($conn);
            ScriptManager::registerScript('courseCategorySelectScript', '', URLGenerator::generateFileUrl('assets/script/CourseCategorySelect.js'));
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    public const NUM_RESULTS_ON_PAGE = 15;

    private array $courses = [];
    private int $courseCount = 0;
    private array $categories = [];
    private bool $isUserAbelMember = false;

    protected function markup(): Component|array|null
    {
        return component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text('Cursos')),
            component(BasicSearchInput::class),
            tag('div', class: 'text-right', children:
            [
                tag('label', children:
                [
                    text('Categoria: '),
                    tag('select', 
                        name: 'category_id', 
                        id: 'courseCategorySelect',
                        children:
                        [
                            tag('option', value: 0, children: text('-- Qualquer --')),
                            ...array_map(fn($cat) => tag('option', value: $cat->id->unwrapOr(0), children: text($cat->title->unwrapOr('')), selected: ($_GET['category_id'] ?? -1) == $cat->id->unwrapOr(0) ), $this->categories)
                        ]
                      )
                ])
            ]),
            component(OrderByLinks::class, linksDefinitions:
            [
                'Nome' => 'name',
                'Carga horária' => 'hours'
            ]),
            component(CourseGrid::class, isUserAbelMember: $this->isUserAbelMember, courses: $this->courses),
            component(Paginator::class, totalItems: $this->courseCount, pageNum: $_GET['page_num'] ?? 1, numResultsOnPage: self::NUM_RESULTS_ON_PAGE)
        ]);
    }
}