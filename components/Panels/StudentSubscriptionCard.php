<?php 
namespace VictorOpusculo\Parlaflix\Components\Panels;

use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Subscription;
use VictorOpusculo\PComp\{View, Component};
use function VictorOpusculo\PComp\Prelude\{tag, text, scTag};

class StudentSubscriptionCard extends Component
{
    protected function setUp()
    {

    }

    protected Subscription $subscription;
    protected string $detailsUrl;

    protected function markup() : Component|array|null
    {
        return tag('a', class: 'block overflow-clip relative p-2 mx-4 mb-4 h-[300px] min-w-[300px] max-w-[400px] rounded border border-neutral-300 dark:border-neutral-700 hover:brightness-75', 
        href: $this->detailsUrl,
        children:
        [
            tag('div', class: 'absolute left-0 right-0 bottom-0 top-0 w-full', children:
                $this->subscription->course->cover_image_media_id->unwrapOr(false) ?
                    scTag('img', class: 'absolute m-auto left-0 right-0 top-0 bottom-0', src: URLGenerator::generateFileUrl($this->subscription->course->coverMedia->fileNameFromBaseDir()))
                :
                    text('Sem imagem!')
            ),
            tag('div', class: 'absolute bottom-0 left-0 right-0 z-10 dark:bg-neutral-700/50 bg-neutral-300/80 p-2 text-center', children: 
            [
                tag('div', children: text($this->subscription->course->name->unwrap())),
                tag('div', class: 'flex flex-row items-center justify-center' , children: 
                [
                    tag('progress', 
                        class: 'w-[calc(100%-50px)] mr-2 [&::-webkit-progress-value]:bg-violet-700 [&::-moz-progress-bar]:bg-violet-700',
                        max: $this->subscription->getOtherProperties()->lessonCount ?? 1,
                        value: $this->subscription->getOtherProperties()->doneLessonCount ?? 0,
                    ),
                    text(number_format((($this->subscription->getOtherProperties()->doneLessonCount ?? 0) / ($this->subscription->getOtherProperties()->lessonCount ?? 1)) * 100, 0) . '%')
                ])
            ])
        ]);
    }
}