<?php
namespace VictorOpusculo\Parlaflix\Api\Student;

use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Student;
use VictorOpusculo\PComp\RouteHandler;

require_once __DIR__ . '/../../lib/Middlewares/StudentLoginCheck.php';
require_once __DIR__ . '/../../lib/Middlewares/JsonBodyParser.php';

class StudentId extends RouteHandler
{
    public function __construct()
    {
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\studentLoginCheck';
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\jsonParser';
    }

    protected $studentId;

    protected function PUT(): void
    {
        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->studentId))
                throw new \Exception("ID inválido!");

            $data = $_POST['data'] ?? [];

            $student = (new Student([ 'id' => $this->studentId ]))
            ->setCryptKey(Connection::getCryptoKey())
            ->getSingle($conn)
            ->setCryptKey(Connection::getCryptoKey());
            $student->fillPropertiesFromFormInput($data);

            if ($student->existsAnotherStudentWithEmail($conn))
                throw new \Exception("E-mail já utilizado por outro estudante!");

            if ($data['students:currpassword'])
            {
                if (!$student->checkPassword($data['students:currpassword']))
                    throw new \Exception("Senha antiga incorreta!");

                if (!$data['students:password'])
                    throw new \Exception("Senha nova não pode ser em branco.");

                $student->hashPassword($data['students:password']);
            }

            $result = $student->save($conn);
            if ($result['affectedRows'] > 0)
            {
                LogEngine::writeLog("Perfil de estudante alterado.");
                $this->json([ 'success' => 'Perfil atualizado com sucesso!' ]);
            }
            else
                $this->json([ 'info' => 'Nenhum dado alterado.' ]);
        }
        catch (\Exception $e)
        {
            LogEngine::writeErrorLog($e->getMessage());
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
    }
}