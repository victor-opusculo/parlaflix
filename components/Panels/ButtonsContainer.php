<?php 
namespace VictorOpusculo\Parlaflix\Components\Panels;

use VictorOpusculo\PComp\Component;
use function VictorOpusculo\PComp\Prelude\tag;

class ButtonsContainer extends Component
{
    protected function markup() : Component|array|null
    {
        return tag('div', class: 'flex lg:flex-row flex-col items-center justify-center', children: $this->children );
    }
}