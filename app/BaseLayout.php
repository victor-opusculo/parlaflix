<?php

namespace VictorOpusculo\Parlaflix\App;

use VictorOpusculo\Parlaflix\Components\Layout\DarkModeToggler;
use VictorOpusculo\Parlaflix\Components\NavBar;
use VictorOpusculo\Parlaflix\Components\PageMessages;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\PComp\Component;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\scTag;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class BaseLayout extends Component
{
    protected function markup(): Component|array|null
    {
        return 
        [
            tag('div', class: 'min-h-[calc(100vh-150px)]', children:
            [
                tag('header', class: 'bg-black mb-4 py-4 text-white font-bold flex flex-col md:flex-row justify-between md:px-8 items-center', children: 
                [
                    scTag('img', width: 128, src: URLGenerator::generateFileUrl('assets/pics/parlaflix_dark.svg')),
                    component(NavBar::class),
                    component(DarkModeToggler::class)
                ]),
                component(PageMessages::class),
                tag('main', children: $this->children)
            ]),
            tag('footer', class: 'flex flex-col md:flex-row justify-center items-center h-[132px] mt-4 bg-black py-4 md:px-8 px-4 text-white', children: 
            [
                scTag('img', class: 'inline-block mr-4', width: 64, src: URLGenerator::generateFileUrl('assets/pics/parlaflix_dark.svg')),
                tag('div', children: 
                [
                    text("Parlaflix - Plataforma EAD da "),
                    tag('a', class: 'hover:underline', href: 'http://portalabel.org.br', children: text('Associação Brasileira das Escolas do Legislativo e de Contas (ABEL)'))
                ])
            ])
        ];
    }
}