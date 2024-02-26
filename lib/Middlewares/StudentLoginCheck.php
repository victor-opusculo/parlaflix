<?php

namespace VictorOpusculo\Parlaflix\Lib\Middlewares;

use VictorOpusculo\Parlaflix\Lib\Helpers\UserTypes;

function studentLoginCheck()
{
    session_name('parlaflix_student_user');
    session_start();

    if (($_SESSION['user_type'] ?? '') !== UserTypes::student)
    {
        session_unset();
        if (isset($_SESSION)) session_destroy();

        header('Content-Type: application/json', true, 401);
        echo json_encode([ 'error' => 'Você não está logado como aluno!' ]);
        exit;
    }
}