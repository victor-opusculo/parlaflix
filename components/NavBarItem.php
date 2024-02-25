<?php
namespace VictorOpusculo\Parlaflix\Components;

use VictorOpusculo\PComp\{View, StyleManager, Component};
use function VictorOpusculo\PComp\Prelude\{tag, component, text};

class NavBarItem extends Component
{
    protected string $url = "#";
    protected string $label;

    protected function setUp()
    {
       
    } 

    protected function markup() : Component
    {
        return tag('span',
        class: '',
        children:
        [
            tag('a', class: 'hover:bg-violet-700 active:bg-violet-800 rounded cursor-pointer inline-block px-4 py-1 md:py-2' , href: $this->url, children: text($this->label))
        ]);
    }
}