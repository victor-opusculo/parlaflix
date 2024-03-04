<?php

namespace VictorOpusculo\Parlaflix\App\Student;

use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class Login extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Log-in de estudante";    
    }

    protected function markup(): Component|array|null
    {
        return
        [
            tag('h1', children: text('Log-in de estudante')),
            tag('student-login-form')
        ];
    }
}