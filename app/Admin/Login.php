<?php

namespace VictorOpusculo\Parlaflix\App\Admin;

use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Helpers\UserTypes;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class Login extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Log-in de administrador"; 
        
        session_name('parlaflix_admin_user');
        session_start();
        if (($_SESSION['user_type'] ?? '') === UserTypes::administrator)
        {
            header('location:' . URLGenerator::generatePageUrl('/admin/panel'), true, 303);
            exit;
        }
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