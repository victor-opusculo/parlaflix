<?php
namespace VictorOpusculo\Parlaflix\Api\Student;

use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
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
                LogEngine::writeLog("Cadastro de estudante feito! Aluno ID: {$result['newId']}.");
                $this->json([ 'success' => 'Cadastro efetuado com sucesso! Você pode entrar com sua conta agora.' ]);
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