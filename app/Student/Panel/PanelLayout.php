<?php
namespace VictorOpusculo\Parlaflix\App\Student\Panel;

use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Helpers\UserTypes;
use VictorOpusculo\PComp\Component;

use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

#[\AllowDynamicProperties]
final class PanelLayout extends Component
{
    protected function setUp()
    {
        session_name('parlaflix_student_user');
        session_start();

        if (!isset( $_SESSION['user_type']) || $_SESSION['user_type'] != UserTypes::student)
        {
            session_unset();
            if (!isset($_SESSION)) session_destroy();
            header('location:' . URLGenerator::generatePageUrl('/student/login', [ 'messages' => 'Estudante não logado!' ]), true, 303);
            exit;
        }
    }

    protected function markup(): Component|array|null
    {
        return 
        [
            tag('div', class: 'p-2 bg-neutral-200 dark:bg-neutral-800 flex flex-row justify-between items-center', children:
            [
                tag('span', children:
                [ 
                    tag('span', class: 'font-bold', children: text('Estudante logado(a): ')),
                    text($_SESSION['user_name'] ?? '***'),
                    ($_SESSION['user_is_member'] ?? false) ? text(" (Associado(a) ABEL)") : null
                ]),
                tag('span', children:
                [
                    tag('a', class: 'btn mr-2 inline-block', href: URLGenerator::generatePageUrl('/student/panel/edit_profile'), children: text('Alterar perfil')),
                    tag('student-logout-button')
                ])
            ]),
            tag('div', children: $this->children)
        ];
    }
}