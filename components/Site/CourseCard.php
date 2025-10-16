<?php 
namespace VictorOpusculo\Parlaflix\Components\Site;

use Exception;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\PComp\{View, Component};
use function VictorOpusculo\PComp\Prelude\{tag, text, scTag};

class CourseCard extends Component
{
    protected function setUp()
    {
    }

    protected Course $course;
    protected string $detailsUrl;
    protected bool $isUserAbelMember = false;

    protected function markup() : Component|array|null
    {
        return tag('a', class: 'block overflow-clip relative p-2 mx-4 mb-4 h-[300px] min-w-[300px] max-w-[400px] rounded-sm border border-neutral-300 dark:border-neutral-700 hover:brightness-75', 
        href: $this->detailsUrl,
        children:
        [
            tag('div', class: 'absolute left-0 right-0 bottom-0 top-0 w-full', children:
                /* !$this->isUserAbelMember && (bool)$this->course->members_only->unwrapOr(0)
                    ? scTag('img', class: 'absolute m-auto left-0 right-0 top-0 bottom-0', src: URLGenerator::generateFileUrl('assets/pics/members_only.png'))
                    : */ ($this->course->cover_image_media_id->unwrapOr(false) 
                        ? scTag('img', class: 'absolute m-auto left-0 right-0 top-0 bottom-0', src: URLGenerator::generateFileUrl($this->course->coverMedia->fileNameFromBaseDir()))
                        : tag('span', class: 'absolute left-0 right-0 bottom-0 top-0 text-center align-middle leading-[300px]', children: text('Sem imagem!')) 
                      )
            ),
            tag('div', class: 'absolute bottom-0 left-0 right-0 z-10 dark:bg-neutral-700/50 bg-neutral-300/80 p-2 text-center', children: 
            [
                tag('div', children: text($this->course->name->unwrap())),
                tag('div', class: 'stars5Mask w-[100px] h-[24px] inline-block text-center', children:
                    tag('progress', class: 'w-full h-full starProgressBar inline', min: 0, max: 5, value: $this->course->surveysAveragePoints)
                ),
                tag('div', class: 'flex flex-row items-center justify-center' , children: 
                [
                    tag('span', children: text(
                        $this->course->hours->unwrap() >= 2 
                        ? Data::formatCourseHourNumber($this->course->hours->unwrap()) . ' horas'
                        : Data::formatCourseHourNumber($this->course->hours->unwrap())  . ' hora'
                    ))
                ])
            ])
        ]);
    }
}
