<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Courses\CourseId;

use VictorOpusculo\Parlaflix\Components\Data\DateTimeTranslator;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Components\Panels\DeleteEntityForm;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Lesson;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestData;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestSkel;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;
use VOpus\PhpOrm\Exceptions\DatabaseEntityNotFound;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class BuildTest extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Excluir curso";

        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->lessonId))
                throw new \Exception('ID inválido!');

            $this->lesson = new Lesson([ 'id' => $this->lessonId ])
            ->getSingle($conn)
            ->informDateTimeZone($_SESSION['user_timezone'] ?? 'America/Sao_Paulo')
            ->fetchCourse($conn);

            $this->skel = new TestSkel([ 'id' => $this->lessonId ])
            ->getFromLessonId($conn);
        }
        catch (DatabaseEntityNotFound $e)
        {
            if ($e->databaseTable !== TestSkel::DB_TABLE)
                Context::getRef('page_messages')[] = $e->getMessage();
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    protected $lessonId;
    private ?Lesson $lesson = null;
    private ?TestSkel $skel = null;

    protected function markup(): Component|array|null
    {
        return isset($this->lesson) ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text('Editar questionário')),
            component(Label::class, label: 'Curso', children: text($this->lesson->course->name->unwrapOr('***'))),
            component(Label::class, label: 'Aula', children: text($this->lesson->title->unwrapOr('***'))),
            component(Label::class, label: 'Nº da aula', children: text($this->lesson->index->unwrapOr('***'))),
        ]) 
        :
        null;
    }
}