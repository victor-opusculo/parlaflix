<?php
namespace VictorOpusculo\Parlaflix\App\Info\Course;

use Exception;
use VictorOpusculo\Parlaflix\Components\Data\DateTimeTranslator;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Helpers\UserTypes;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Student;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Subscription;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;
use VictorOpusculo\PComp\ScriptManager;

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

            ScriptManager::registerScript('share_script', <<<JAVASCRIPT
                const { ShareUrl, ShareUrlAuto } = await import(Parlaflix.Helpers.URLGenerator.generateFileUrl("assets/script/share/share-url.js"));
                const ShareUrlShare = ShareUrl({
                    selector: '#share',
                    textSelector: 'span',
                    textSuccess: "Compartilhar"
                });
            JAVASCRIPT, "", true);

            HeadManager::$title = $this->course->name->unwrapOr('Curso');

            
            $this->studentLoggedIn = (($_SESSION['user_type'] ?? '') === UserTypes::student) && (!empty($_SESSION['user_id']));

            if ($this->studentLoggedIn)
            {
                $subsGetter = (new Subscription([ 'student_id' => $_SESSION['user_id'], 'course_id' => $this->courseId ]));
                $this->studentAlreadySubscribed = $subsGetter->isStudentSubscribed($conn);

                $isMember = (bool)($_SESSION['user_is_member'] ?? false);
                $isCourseForMembers = (bool)$this->course->members_only->unwrapOr(0);
                if ($isCourseForMembers && !$isMember)
                {
                    $this->course = null;
                    throw new Exception("Curso exclusivo para associados!");
                }
            }
            else
            {
                $isCourseForMembers = (bool)$this->course->members_only->unwrapOr(0);
                if ($isCourseForMembers)
                {
                    $this->course = null;
                    throw new Exception("Curso exclusivo para associados!");
                }
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
    protected mixed $courseId;

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
                    component(Label::class, label: 'Carga horária', labelBold: true, children: text(Data::formatCourseHourNumber($this->course->hours->unwrapOr(0)) . "h")),
                    component(Label::class, label: 'Categoria(s)', labelBold: true, children: text(array_reduce($this->course->categoriesJoints, fn($carry, $j) => ($carry ? $carry . ', ' : '') . $j->getOtherProperties()->title, null))),

                    $this->studentLoggedIn
                        ? ($this->studentAlreadySubscribed
                            ? tag('p', class: 'text-center font-bold p-8', children: text('Você já está inscrito neste curso.'))
                            : tag('course-subscribe-button', courseid: $this->course->id->unwrap()))
                        : tag('div', class: 'text-center p-8', children:
                                tag('a', class: 'btn', href: URLGenerator::generatePageUrl('/student/login', [ 'back_to' => $_GET['page'] ]), children: text('Inscrever-se'))
                    ),

                    tag('div', class: 'text-center', children: 
                        tag('button', class: 'btn', type: 'button', id: 'share', children:
                        [
                            scTag('img', width: 24, height: 24, class: 'inline-block invert mr-2', src: URLGenerator::generateFileUrl('assets/pics/share.svg')),
                            tag('span', children: text("Compartilhar")) 
                        ])
                    )
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