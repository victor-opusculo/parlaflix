<?php

namespace VictorOpusculo\Parlaflix\App\Student;

use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
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
            tag('student-login-form'),
            tag('div', class: 'text-center', children:
            [
                tag('a', class: 'link block', href: URLGenerator::generatePageUrl('/student/register'), children: text('Não tem conta? Criar conta.')),
                tag('a', class: 'link block', href: URLGenerator::generatePageUrl('/student/recover_password'), children: text('Esqueci minha senha!'))
            ])
        ];
    }
}