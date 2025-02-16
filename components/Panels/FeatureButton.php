<?php 
namespace VictorOpusculo\Parlaflix\Components\Panels;

use VictorOpusculo\PComp\Component;
use function VictorOpusculo\PComp\Prelude\{ tag, scTag, text };

class FeatureButton extends Component
{
    protected string $url = '';
    protected string $label;
    protected string $iconUrl;
    protected string $additionalClasses = '';
    protected bool $invertIconColorsOnDark = false;

    protected function markup() : Component|array|null
    {
        return tag('a', 
        class: 'flex flex-col items-center justify-center w-40 h-40 border border-neutral-700 rounded-sm hover:backdrop-brightness-75 mr-2 mb-2 ' . $this->additionalClasses, 
        href: $this->url, 
        children: 
        [ 
            scTag('img', class: 'block h-20 mb-2' . ($this->invertIconColorsOnDark ? ' dark:invert' : ''), src: $this->iconUrl, alt: $this->label),
            tag('div', class: 'text-center', children: text($this->label) ) 
        ]);
    }
}