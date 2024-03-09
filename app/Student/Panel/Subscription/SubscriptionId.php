<?php
namespace VictorOpusculo\Parlaflix\App\Student\Panel\Subscription;

use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Components\Layout\FlexSeparator;
use VictorOpusculo\Parlaflix\Components\Panels\StudentLessonViewer;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Lesson;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Subscription;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;
use VictorOpusculo\PComp\ScriptManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\scTag;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class SubscriptionId extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Curso";
        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->subscriptionId))
                throw new \Exception("ID inválido!");

            $this->subscription = (new Subscription([ 'id' => $this->subscriptionId, 'student_id' => $_SESSION['user_id'] ?? 0 ]))
            ->getSingleFromStudent($conn)
            ->fetchCourse($conn);

            $this->subscription->course
            ->informDateTimeZone($_SESSION['user_timezone'] ?? 'America/Sao_Paulo')
            ->fetchLessons($conn)
            ->fetchCoverMedia($conn)
            ->fetchCategoriesJoints($conn);

            HeadManager::$title = $this->subscription->course->name->unwrapOr('Curso');

            $this->approved = ($this->subscription->getOtherProperties()->studentPoints ?? 0) >= ($this->subscription->course->min_points_required->unwrapOr(INF));

            $currLess = array_filter($this->subscription->course->lessons, fn($less) => $less->index->unwrapOr(0) == $_GET['lesson_index'] ?? INF);
            if (count($currLess) > 0)
                $this->loadedLesson = array_pop($currLess);

            ScriptManager::registerScript("subscriptionLessonsListScript", 
                "window.addEventListener('load', function()
                {
                    let showLessonList = true;
                    const btn = document.getElementById('subscriptionLessonsListToggleButton');
                    
                    if (btn)
                    btn.onclick = () => 
                    {
                        showLessonList = !showLessonList;

                        if (showLessonList)
                        {
                            document.getElementById('subscriptionLessonsList').classList.remove('hidden');
                            document.getElementById('subscriptionLessonsList').classList.add('flex');
                            document.getElementById('subscriptionCurrentLesson').classList.add('hidden');
                        }
                        else
                        {
                            document.getElementById('subscriptionLessonsList').classList.add('hidden');
                            document.getElementById('subscriptionLessonsList').classList.remove('flex');
                            document.getElementById('subscriptionCurrentLesson').classList.remove('hidden');
                        }
                    };
                })"
            );
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    protected $subscriptionId;
    private bool $approved = false;
    private ?Subscription $subscription = null;
    private ?Lesson $loadedLesson = null;

    protected function markup(): Component|array|null
    {
        return isset($this->subscription) ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text($this->subscription->course->name->unwrapOr('Curso'))),

            tag('section', class: 'rounded border border-neutral-300 dark:border-neutral-700 p-4 m-2 w-full flex md:flex-row flex-col bg-neutral-100 dark:bg-neutral-800', children:
                [
                    tag('div', class: 'block w-[8rem] h-[8rem] relative', children:
                    [
                        scTag('img', 
                        class: 'absolute top-0 bottom-0 left-0 right-0 my-auto',
                        src: $this->subscription->course->coverMedia 
                            ? URLGenerator::generateFileUrl($this->subscription->course->coverMedia->fileNameFromBaseDir())
                            : URLGenerator::generateFileUrl('assets/pics/nopic.png')
                        )
                    ]),
                    component(FlexSeparator::class),
                    tag('span', class: 'text-lg font-bold flex items-center max-w-[300px]', children: text($this->subscription->course->name->unwrapOr("***"))),
                    component(FlexSeparator::class),
                    tag('div', children:
                    [
                        component(Label::class, labelBold: true, label: "Progresso", children:
                        [
                            tag('progress', 
                                class: 'my-2 mr-2 [&::-webkit-progress-value]:bg-violet-700 [&::-moz-progress-bar]:bg-violet-700', 
                                value: $this->subscription->getOtherProperties()->doneLessonCount ?? 0, 
                                max: $this->subscription->getOtherProperties()->lessonCount ?? 1
                            ),
                            tag('span', class: 'my-1', children: text((number_format(($this->subscription->getOtherProperties()->doneLessonCount ?? 0) / ($this->subscription->getOtherProperties()->lessonCount ?? 1) * 100, 2, ',')) . '%'))
                        ]),
                        component(Label::class, labelBold: true, label: "Aulas vistas", children:
                        [
                            text(($this->subscription->getOtherProperties()->doneLessonCount ?? 0) . ' de ' . ($this->subscription->getOtherProperties()->lessonCount ?? 0))
                        ]),
                        component(Label::class, labelBold: true, label: "Pontuação marcada", children:
                        [
                            text(($this->subscription->getOtherProperties()->studentPoints ?? 0) . ' de ' . ($this->subscription->getOtherProperties()->maxPoints ?? 0) . ' requerido')
                        ]),
                        component(Label::class, labelBold: true, label: "Resultado", children:
                        [
                            ($this->subscription->getOtherProperties()->studentPoints ?? 0) >= ($this->subscription->course->min_points_required->unwrapOr(INF)) ?
                                tag('span', class: ($this->approved ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300'), children:
                                [
                                    scTag('img', width: 24, height: 24, class: 'inline-block mr-2', src: $this->approved ? URLGenerator::generateFileUrl('assets/pics/check.png') : URLGenerator::generateFileUrl('assets/pics/wrong.png')),
                                    text($this->approved ? 'Aprovado!' : 'Reprovado!')
                                ])
                            :
                                text("Em progresso")
                        ]),
                        $this->approved ?
                            component(Label::class, labelBold: true, label: "Certificado", children:
                            [
                                tag('a', class: 'btn', href: URLGenerator::generateScriptUrl('certificate/generate.php', [ 'subscription_id' => $this->subscription->id->unwrapOr(0) ]), children: text('Gerar'))
                            ])
                        :
                        null
                    ])
                ]),
                            
                tag('section', class: 'flex md:flex-row flex-col rounded border border-neutral-300 dark:border-neutral-700 p-4 m-2 w-full bg-neutral-100 dark:bg-neutral-800', children:
                [
                    tag('button', class: 'inline md:hidden btn', id: 'subscriptionLessonsListToggleButton', type: 'button', children: text('Ver/ocultar aulas')),
                    tag('div',
                        id: 'subscriptionLessonsList',
                        class: 'flex-[25%] flex md:flex flex-col mr-4 bg-neutral-200 dark:bg-neutral-900 p-2 rounded-lg',
                        children: array_map( fn($less) => tag('a', 
                                                            class: 'block p-2 font-bold hover:backdrop-brightness-75 active:backdrop-brightness-50',
                                                            href: URLGenerator::generatePageUrl("/student/panel/subscription/{$this->subscription->id->unwrapOr(0)}", [ 'lesson_index' => $less->index->unwrapOr(0) ]),
                                                            children: text("{$less->index->unwrapOr(0)}. {$less->title->unwrapOr('Aula')}")
                        ), $this->subscription->course->lessons)
                    ),
                    tag('div',
                        class: 'flex-[75%] hidden md:block',
                        id: 'subscriptionCurrentLesson',
                        children:
                        [
                            isset($this->loadedLesson) 
                                ? [
                                    tag('h2', children: text("{$this->loadedLesson->index->unwrapOr(0)}. {$this->loadedLesson->title->unwrapOr('Aula')}")),
                                    component(StudentLessonViewer::class, lesson: $this->loadedLesson)
                                ]
                                : text('Selecione uma aula')
                        ]
                    )
                ])
        ])
        : null;
    }
}