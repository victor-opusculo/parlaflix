<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Courses\CourseId;

use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Category;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
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
        HeadManager::$title = "Editar curso";

        $conn = Connection::get();
        try
        {
            $this->course = (new Course([ 'id' => $this->courseId ]))
            ->getSingle($conn)
            ->fetchLessons($conn)
            ->fetchCategoriesJoints($conn);

            $this->categoriesAvailable = (new Category)->getAll($conn);
            $this->courseCategoryIds = array_map(fn($joint) => $joint->category_id->unwrapOr(0), $this->course->categoriesJoints);
            $this->courseLessons = $this->course->lessons;
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    protected $courseId;
    private ?Course $course = null;
    private array $categoriesAvailable;
    private array $courseCategoryIds; 
    private array $courseLessons; 

    protected function markup(): Component|array|null
    {
        return isset($this->course) ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text('Editar curso')),
            tag('edit-course-form',
                ...json_decode(json_encode($this->course), true),

                categories_available_json: Data::hscq(json_encode($this->categoriesAvailable)),
                categories_ids_json: Data::hscq(json_encode($this->courseCategoryIds)),
                lessons_json: Data::hscq(json_encode($this->courseLessons))
            )
        ]) 
        :
        null;
    }
}