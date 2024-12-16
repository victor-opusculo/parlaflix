<?php
namespace VictorOpusculo\Parlaflix\Api\Administrator\Panel\Students;

use Exception;
use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Student;
use VictorOpusculo\PComp\RouteHandler;
use VOpus\PhpOrm\Option;

require_once __DIR__ . '/../../../../lib/Middlewares/AdminLoginCheck.php';
require_once __DIR__ . '/../../../../lib/Middlewares/JsonBodyParser.php';

final class StudentId extends RouteHandler
{
    public function __construct()
    {
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\adminLoginCheck';
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\jsonParser';
    }

    protected $studentId;

    protected function PUT(): void
    {
        $conn = Connection::get();
        try
        {
            $data = $_POST['data'] ?? [];

            $isMember = $data['students:is_abel_member'] ?? false;

            $student = (new Student([ 'id' => $this->studentId ]))
            ->setCryptKey(Connection::getCryptoKey())
            ->getSingle($conn)
            ->setCryptKey(Connection::getCryptoKey())
            ->fillPropertiesFromFormInput($data);

            $student->is_abel_member = $isMember ? 1 : 0;

            $result = $student->save($conn);
            if ($result['affectedRows'] > 0)
            {
                LogEngine::writeLog("Estudante alterado. Estudante ID: " . $this->studentId);
                $this->json([ 'success' => "Estudante alterado com sucesso!" ]);
            }
            else
            {
                $this->json([ 'info' => "Nenhum dado alterado." ]);
            }
        }
        catch (Exception $e)
        {
            LogEngine::writeErrorLog($e->getMessage());
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
    }

    protected function DELETE(): void
    {
        $conn = Connection::get();
        try
        {
            $student = (new Student([ 'id' => $this->studentId ]))
            ->setCryptKey(Connection::getCryptoKey())
            ->getSingle($conn);

            $result = $student->delete($conn);
            if ($result['affectedRows'] > 0)
            {
                LogEngine::writeLog("Estudante excluído. Estudante ID: " . $this->studentId);
                $this->json([ 'success' => "Estudante excluído com sucesso!" ]);
            }
            else
            {
                $this->json([ 'error' => "Não foi possível excluir o estudante." ]);
            }
        }
        catch (Exception $e)
        {
            LogEngine::writeErrorLog($e->getMessage());
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
    }
}