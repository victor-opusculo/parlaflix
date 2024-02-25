<?php

namespace VictorOpusculo\Parlaflix\App\Admin;

use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class Login extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Log-in de administrador";    
    }

    protected function markup(): Component|array|null
    {
        return
        [
            tag('h1', children: text('Log-in de administrador')),
            tag('admin-login-form')
        ];
    }
}