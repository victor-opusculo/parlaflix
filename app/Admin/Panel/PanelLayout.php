<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel;

use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Helpers\UserTypes;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;

use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

#[\AllowDynamicProperties]
final class PanelLayout extends Component
{
    protected function setUp()
    {
        session_name('parlaflix_admin_user');
        session_start();

        if (!isset( $_SESSION['user_type']) || $_SESSION['user_type'] != UserTypes::administrator)
        {
            session_unset();
            if (!isset($_SESSION)) session_destroy();
            header('location:' . URLGenerator::generatePageUrl('/admin/login', [ 'messages' => 'Administrador não logado!' ]), true, 303);
            exit;
        }

        setcookie("admin_logged_in", "1", 0, "/");
    }

    protected function markup(): Component|array|null
    {
        return 
        [
            tag('div', class: 'p-2 bg-neutral-200 dark:bg-neutral-800 flex flex-row justify-between items-center', children:
            [
                tag('span', children:
                [ 
                    tag('span', class: 'font-bold', children: text('Administrador(a) logado(a): ')),
                    text($_SESSION['user_name'] ?? '***')
                ]),
                tag('span', children:
                [
                    tag('a', class: 'btn mr-2 inline-block', href: URLGenerator::generatePageUrl('/admin/panel'), children: text('Home')),
                    tag('a', class: 'btn mr-2 inline-block', href: URLGenerator::generatePageUrl('/admin/panel/edit_profile'), children: text('Alterar perfil')),
                    tag('admin-logout-button')
                ])
            ]),
            tag('div', children: $this->children)
        ];
    }
}