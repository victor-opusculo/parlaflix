<?php
namespace VictorOpusculo\Parlaflix\App\Info\Course;

use VictorOpusculo\Parlaflix\Components\Data\DateTimeTranslator;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Helpers\UserTypes;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Subscription;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\rawText;
use function VictorOpusculo\PComp\Prelude\scTag;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class CourseId extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Visualizar curso";
        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->courseId))
                throw new \Exception("ID inválido!");

            session_name('parlaflix_student_user');
            session_start();

            $this->course = (new Course([ 'id' => $this->courseId ]))
            ->getSingleVisibleOnly($conn)
            ->informDateTimeZone($_SESSION['user_timezone'] ?? 'America/Sao_Paulo')
            ->fetchLessons($conn)
            ->fetchCategoriesJoints($conn)
            ->fetchCoverMedia($conn);

            HeadManager::$title = $this->course->name->unwrapOr('Curso');

            
            $this->studentLoggedIn = (($_SESSION['user_type'] ?? '') === UserTypes::student) && (!empty($_SESSION['user_id']));

            if ($this->studentLoggedIn)
            {
                $subsGetter = (new Subscription([ 'student_id' => $_SESSION['user_id'], 'course_id' => $this->courseId ]));
                $this->studentAlreadySubscribed = $subsGetter->isStudentSubscribed($conn);
            }
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    private bool $studentLoggedIn = false;
    private bool $studentAlreadySubscribed = false;
    private ?Course $course = null; 
    protected $courseId;

    protected function markup(): Component|array|null
    {
        return isset($this->course) ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text($this->course->name->unwrapOr(''))),
            tag('section', class: 'flex md:flex-row flex-col', children:
            [
                tag('div', class: 'flex-[30%]', children: 
                    scTag('img', 
                        src: URLGenerator::generateFileUrl(isset($this->course->coverMedia) ? $this->course->coverMedia->fileNameFromBaseDir() : '/assets/pics/nopic.png'), 
                        alt: Data::hscq($this->course->name->unwrapOr('Curso'))
                    )),
                tag('div', class: 'flex-[70%] px-4', children:
                [
                    component(Label::class, label: 'Descrição', labelBold: true, lineBreak: true, children: rawText(nl2br(Data::hsc($this->course->presentation_html->unwrapOr('Sem descrição'))))),
                    component(Label::class, label: 'Carga horária', labelBold: true, children: text($this->course->hours->unwrapOr('Indefinido'))),
                    component(Label::class, label: 'Categoria(s)', labelBold: true, children: text(array_reduce($this->course->categoriesJoints, fn($carry, $j) => ($carry ? $carry . ', ' : '') . $j->getOtherProperties()->title, null))),

                    $this->studentLoggedIn
                        ? ($this->studentAlreadySubscribed
                            ? tag('p', class: 'text-center font-bold p-8', children: text('Você já está inscrito neste curso.'))
                            : tag('course-subscribe-button', courseid: $this->course->id->unwrap()))
                        : tag('p', class: 'text-center font-bold p-8', children: text('Entre com sua conta de estudante para se inscrever neste curso.'))
                ])
            ]),
            tag('h2', children: text('Aulas')),
            tag('ol', class: 'list-decimal pl-8', children:
                array_map( fn($less) => tag('li', children: 
                [
                    text($less->title->unwrapOr('***')),
                    ($less->live_meeting_datetime->unwrapOr(false) 
                    ?   [ text(' | Ao vivo em: '), component(DateTimeTranslator::class, isoDateTime: $less->live_meeting_datetime->unwrap()) ]
                    :   null
                    )
                ]), $this->course->lessons)
            )
        ])
        : null;
    }
}