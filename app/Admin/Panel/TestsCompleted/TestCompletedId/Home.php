<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\TestsCompleted\TestcompletedId;

use Exception;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\LessonTests;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestCompleted;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestData;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestQuestion;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestQuestionOption;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\scTag;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class Home extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Ver questionário respondido";
        
        $conn = Connection::get();
        try
        {  
            if (!Connection::isId($this->testCompletedId))
                throw new Exception("ID inválido!");

            $this->test = (new TestCompleted([ 'id' => $this->testCompletedId]))
            ->getSingle($conn)
            ->fetchLesson($conn)
            ->fetchSubscription($conn)
            ->fetchSkel($conn);

            $this->test->lesson->fetchCourse($conn);
            $this->testStruct = $this->test->buildStructure();
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    protected $testCompletedId;
    private ?TestCompleted $test = null;
    private ?TestData $testStruct = null;


    protected function markup(): Component|array|null
    {
        return isset($this->test) ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text("Ver questionário respondido")),

            component(Label::class, labelBold: true, label: "ID", children: text($this->test->id->unwrapOr("***"))),
            component(Label::class, labelBold: true, label: "Curso", children: 
                tag('a', class: 'link', href: URLGenerator::generatePageUrl("/admin/panel/courses/{$this->test->lesson->course_id->unwrapOr(0)}"), children:
                    text($this->test->lesson->course->name->unwrapOr("***"))
                )
            ),
            component(Label::class, labelBold: true, label: "Aula", children: text($this->test->lesson->title->unwrapOr("***"))),
            component(Label::class, labelBold: true, label: "Nota", children: text(
                    number_format(
                        LessonTests::calculateGrade(
                            LessonTests::calculateCorrectAnswers($this->test->buildStructure())
                        ),
                        1, ',', '.'
                    ) . "%"
                ),
            ),
            component(Label::class, labelBold: true, label: "Mínimo requerido", children: text($this->test->skel->min_percent_for_approval->unwrapOr("***") . "%")),

            tag('h2', children: text("Questões")),

            tag('ol', class: 'list-decimal pl-8', children:
                array_map(fn(TestQuestion $quest) => 
                    tag('li', children:
                    [
                        component(Label::class, labelBold: true, label: 'Enunciado', children: text($quest->text)),
                        component(Label::class, labelBold: true, label: 'Figura', children: 
                            $quest->pictureMediaId 
                            ? tag('a', class: 'link', href: URLGenerator::generatePageUrl("/admin/panel/media/{$quest->pictureMediaId}"), children: text("Mídia " . $quest->pictureMediaId))
                            : text("Nenhuma")   
                        ),

                        component(Label::class, labelBold: true, label: 'Resultado', children: text(LessonTests::questionResult($quest) ? "Correta" : "Incorreta")),

                        tag("ol", class: 'list-[lower-alpha] my-4 pl-8', children:
                            array_map(fn(TestQuestionOption $opt) => tag('li', children:
                            [
                                text($opt->text),
                                $opt->pictureMediaId ? tag('a', class: 'link', href: URLGenerator::generatePageUrl("/admin/panel/media/{$opt->pictureMediaId}"), children: text("Mídia " . $opt->pictureMediaId)) : null,
                                $opt->isCorrect ? tag('span', class: 'font-bold ml-2', children: text("(Gabarito)")) : null,
                                $opt->studentSelected ? tag('span', class: 'ml-2 italic ' . ($opt->isCorrect ? 'text-green-500' : 'text-red-500'), children: text("<< Assinalada >>")) : null
                            ])
                            ,$quest->options)
                        ),

                        scTag('hr')
                    ])
                , $this->testStruct->questions)
            )
        ])
        : null;
    }
}