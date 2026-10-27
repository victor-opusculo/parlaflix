<?php
namespace VictorOpusculo\Parlaflix\Components\Panels;

use VictorOpusculo\PComp\Component;

use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

class VideoRendererLite extends Component
{
    protected string $videoHost;
    protected string $videoCode;
    protected string $title = '';

    protected int $width = 560;
    protected int $height = 315;

    protected function markup(): Component|array|null
    {
        return tag('div', class: 'mb-4 w-full', children:
        [   
            ($this->videoHost === 'youtube' && $this->videoCode ?
                tag('div', class: 'flex mx-auto max-w-[800px]', children:
                    tag('lite-youtube', videoid: $this->videoCode, children:
                        tag('a', href: "https://youtube.com/watch?v={$this->videoCode}", class: 'lyt-playbtn', title: "Reproduzir vídeo", children:
                            tag('span', class: 'lyt-visually-hidden', children: text($this->title))
                        )
                    )
                )
            :
                null)
        ]); 
    }
}