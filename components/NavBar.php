<?php
namespace VictorOpusculo\Parlaflix\Components;

use VictorOpusculo\Parlaflix\Components\Layout\DarkModeToggler;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Helpers\UserTypes;
use VictorOpusculo\PComp\{Component, Context, ScriptManager};
use function VictorOpusculo\PComp\Prelude\{tag, component, scTag, text};

class NavBar extends Component
{
    protected function setUp()
    {
       ScriptManager::registerScript('darkModeTogglerScript', 
            "window.addEventListener('load', () => document.getElementById('darkModeToggler').checked = window.localStorage.darkMode === '1');");

        if (mb_strpos($_GET['page'] ?? "/", "admin/") !== false)
            $this->isAdmin = true;
    } 

    private bool $isAdmin = false;

    protected function markup() : Component
    {
        return tag('div', children: 
        [
            component(NavBarItem::class, url: URLGenerator::generatePageUrl('/'), label: 'Início'),

            $this->isAdmin
                ? component(NavBarItem::class, url: URLGenerator::generatePageUrl('/admin/panel'), label: 'Home Admin')
                : 
                [
                    component(NavBarItem::class, url: URLGenerator::generatePageUrl('/info/category'), label: 'Cursos'),
                    component(NavBarItem::class, url: URLGenerator::generatePageUrl('/student/panel'), label: 'Área do estudante'),
                    component(NavBarItem::class, url: URLGenerator::generatePageUrl('/certificate/auth'), label: 'Verificar certificado'),
                    ($_COOKIE['admin_logged_in'] ?? "") == "1"
                        ? component(NavBarItem::class, url: URLGenerator::generatePageUrl('/admin/panel'), label: 'Home Admin')
                        : null
                ]
        ]);
    }
}