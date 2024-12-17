<?php
namespace VictorOpusculo\Parlaflix\Api\Student;

use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Settings\MainInboxMail;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Student;
use VictorOpusculo\PComp\RouteHandler;

require_once __DIR__ . '/../../lib/Middlewares/JsonBodyParser.php';


class Register extends RouteHandler
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
            $student = new Student();
            $student->setCryptKey(Connection::getCryptoKey());
            $student->fillPropertiesFromFormInput($_POST['data']);

            if ($student->existsEmail($conn))
                throw new \Exception("O e-mail informado já está cadastrado!");

            $student->hashPassword($_POST['data']['students:password']);

           $result = $student->save($conn);
            
            if ($result['newId'])
            {
                $isMember = (bool)($_POST['data']['students:is_abel_member']);
                if ($isMember)
                {
                    $email = $student->email->unwrapOr("");
                    $fullname = $student->full_name->unwrapOr("");
                    MainInboxMail::sendEmail($conn, $email, $fullname);
                }
                
                LogEngine::writeLog("Cadastro de estudante feito! Aluno ID: {$result['newId']}.");
                $this->json([ 'success' => match ($isMember)
                {
                    true => 'Cadastro efetuado com sucesso! Você pode entrar com sua conta agora. A ABEL foi notificada para avaliar seu status de associado. Assim que confirmarmos, você terá acesso a todos os cursos.',
                    false => 'Cadastro efetuado com sucesso! Você pode entrar com sua conta agora.'
                }]);
            }
            else
                throw new \Exception("Não foi possível criar o cadastro!");
        }
        catch (\Exception $e)
        {
            LogEngine::writeErrorLog($e->getMessage());
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
    }
}