<?php

namespace VictorOpusculo\Parlaflix\Api\Student;

use Exception;
use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Helpers\UserTypes;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Student;
use VictorOpusculo\PComp\RouteHandler;

require_once __DIR__ . '/../../lib/Middlewares/JsonBodyParser.php';

final class Login extends RouteHandler
{
    public function __construct()
    {
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\jsonParser';
    }

    protected function POST(): void
    {
        $conn = Connection::get();
        try
        {
            session_name("parlaflix_student_user");
            session_start();

            $studentGetter = (new Student([ 'email' => $_POST['data']['email'] ?? 'n@d' ]))->setCryptKey(Connection::getCryptoKey());
            $student = $studentGetter->getByEmail($conn);

            if ($student->checkPassword($_POST['data']['password'] ?? '***'))
            {
                $_SESSION['user_type'] = UserTypes::student;
                $_SESSION['user_id'] = $student->id->unwrap();
                $_SESSION['user_email'] = $student->email->unwrap();
                $_SESSION['user_name'] = $student->full_name->unwrapOr("Nome não definido");
                $_SESSION['user_timezone'] = $student->timezone->unwrapOr("America/Sao_Paulo");
                $_SESSION['user_is_member'] = (bool)$student->is_abel_member->unwrapOr(0);
                LogEngine::writeLog("Log-in de estudante realizado: ID {$student->id->unwrapOr(0)}");
                $this->json([ 'success' => 'Bem-vindo!' ]);
            }
            else
                throw new Exception("Senha incorreta!");
        }
        catch (Exception $e)
        {
            LogEngine::writeErrorLog($e->getMessage());
            session_unset();
            if (isset($_SESSION)) session_destroy();
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
        exit;
    }
}