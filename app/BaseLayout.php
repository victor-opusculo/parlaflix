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

#[\AllowDynamicProperties]
final class BaseLayout extends Component
{
    protected function markup(): Component|array|null
    {
        return 
        [
            tag('div', class: 'min-h-[calc(100vh-150px)]', id: "mainPageDiv", children:
            [
                tag('header', class: 'bg-black py-4 text-white font-bold flex flex-col md:flex-row justify-between md:px-8 items-center', children: 
                [
                    tag('div', class: 'flex flex-row items-center', children:
                    [
                        scTag('img', class:'inline-block mr-4', width: 128, src: URLGenerator::generateFileUrl('assets/pics/parlaflix_dark.svg')),          
                        tag('a', class:'inline-block align-center', href: "https://www.portalabel.org.br", children: scTag('img', width: 200, src: URLGenerator::generateFileUrl('assets/pics/abel_dark.png')),)
                        
                    ]),
                    component(NavBar::class),
                    component(DarkModeToggler::class)
                ]),
                component(PageMessages::class),
                tag('main', children: $this->children)
            ]),

            tag('dialog', id: 'messageBox', class: 'backdrop:backdrop-blur m-auto', children:
            [
                    tag('form', method: 'dialog', class: 'text-center min-w-[350px] p-4 dark:text-white dark:bg-zinc-800', children:
                    [
                        tag('h3', class: 'font-bold text-[1.2rem]', id: 'messageBox_title'),
                        tag('p', class: 'my-4', id: 'messageBox_message'),
                        tag('button', value: 'ok', class: 'hidden btn mr-2', children: text('Ok')),
                        tag('button', value: 'cancel', class: 'hidden btn mr-2', children: text('Cancelar')),
                        tag('button', value: 'yes', class: 'hidden btn mr-2', children: text('Sim')),
                        tag('button', value: 'no', class: 'hidden btn', children: text('Não')),
                    ])
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