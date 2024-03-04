<?php
namespace VictorOpusculo\Parlaflix\Components\Panels;

use VictorOpusculo\PComp\Component;

use function VictorOpusculo\PComp\Prelude\tag;

class VideoRenderer extends Component
{
    protected string $videoHost;
    protected string $videoCode;
    protected string $title = '';

    protected function markup(): Component|array|null
    {
        return tag('div', class: 'mb-4', children:
        [   
            ($this->videoHost === 'youtube' && $this->videoCode ?
                tag('div', class: 'flex mx-auto max-w-[600px]', children:
                    tag('iframe', class: 'flex-1', width: 560, height: 315, src: "https://www.youtube.com/embed/{$this->videoCode}",
                        title: $this->title,
                        frameborder: 0,
                        allowfullscreen: true
                    )
                )
            :
                null)
        ]); 
    }
}