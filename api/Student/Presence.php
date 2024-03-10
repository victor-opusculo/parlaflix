<?php
namespace VictorOpusculo\Parlaflix\Api\Student;

use Exception;
use VictorOpusculo\Parlaflix\Lib\Exceptions\LessonPasswordIncorrect;
use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\StudentLessonPassword;
use VictorOpusculo\PComp\RouteHandler;

require_once __DIR__ . '/../../lib/Middlewares/StudentLoginCheck.php';
require_once __DIR__ . '/../../lib/Middlewares/JsonBodyParser.php';


class Presence extends RouteHandler
{
    public function __construct()
    {
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\studentLoginCheck';
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\jsonParser';
    }

    protected function POST(): void
    {
        $conn = Connection::get();
        try
        {
            $studentLessPass = (new StudentLessonPassword)
            ->fillPropertiesFromFormInput($_POST['data'] ?? []);

            if ($studentLessPass->existsByStudentAndLessonId($conn))
            {
                LogEngine::writeErrorLog("Presença já marcada! Aula ID: " . $studentLessPass->lesson_id->unwrapOr(0));
                $this->json([ 'info' => 'Presença/visualização já está marcada!' ]);
                exit;
            }

            $result = $studentLessPass->save($conn);
            if ($result['newId'])
            {
                LogEngine::writeLog('Presença/visualização de aula marcada com sucesso! Aula ID: ' . $studentLessPass->lesson_id->unwrapOr(0));
                $this->json([ 'success' => 'Senha correta! Presença/visualização marcada com sucesso!' ]);
            }
            else
                throw new Exception("Não foi possível gravar presença!");
        }
        catch (LessonPasswordIncorrect $e)
        {
            LogEngine::writeErrorLog($e->getMessage() . ' Aula ID: ' . $e->lessonId);
            $this->json([ 'error' => $e->getMessage() ]);
        }
        catch (\Exception $e)
        {
            LogEngine::writeErrorLog($e->getMessage());
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
    }
}