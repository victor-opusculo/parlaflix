<?php
namespace VictorOpusculo\Parlaflix\Components;

use VictorOpusculo\Parlaflix\Components\Layout\DarkModeToggler;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\PComp\{Component, ScriptManager};
use function VictorOpusculo\PComp\Prelude\{tag, component, scTag, text};

class NavBar extends Component
{
    protected function setUp()
    {
       ScriptManager::registerScript('darkModeTogglerScript', 
            "window.addEventListener('load', () => document.getElementById('darkModeToggler').checked = window.localStorage.darkMode === '1');");
    } 

    protected function markup() : Component
    {
        return tag('div', children: 
        [
            component(NavBarItem::class, url: URLGenerator::generatePageUrl('/'), label: 'Início'),
            component(NavBarItem::class, url: URLGenerator::generatePageUrl('/infos/courses'), label: 'Cursos'),
            component(NavBarItem::class, url: URLGenerator::generatePageUrl('/student/panel'), label: 'Meu aprendizado'),
            component(NavBarItem::class, url: URLGenerator::generatePageUrl('/certificate/auth'), label: 'Verificar certificado')
        ]);
    }
}