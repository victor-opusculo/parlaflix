<?php
namespace VictorOpusculo\Parlaflix\Api\Administrator\Panel\Presences;

use Exception;
use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Lesson;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\StudentLessonPassword;
use VictorOpusculo\PComp\RouteHandler;

require_once __DIR__ . '/../../../../lib/Middlewares/AdminLoginCheck.php';
require_once __DIR__ . '/../../../../lib/Middlewares/JsonBodyParser.php';

final class Mark extends RouteHandler
{
    public function __construct()
    {
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\adminLoginCheck';
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\jsonParser';
    }

    protected function POST(): void
    {
        [ 'lessonId' => $lessonId, 'studentId' => $studentId ] = $_POST;

        $conn = Connection::get();
        try
        {
            $pres = (new StudentLessonPassword([ 'student_id' => $studentId, 'lesson_id' => $lessonId ]));

            if ($pres->existsByStudentAndLessonId($conn))
                throw new Exception("Presença já marcada para este estudante!");

            $less = (new Lesson([ 'id' => $lessonId ]))->getSingle($conn);

            $pres->given_password = $less->completion_password->unwrapOr("");
            $pres->is_correct = 1;

            $result = $pres->save($conn);
            if ($result['newId'])
            {
                LogEngine::writeLog("Presença marcada para estudante ID {$studentId}, aula ID {$lessonId}");
                $this->json([ 'success' => "Presença marcada com sucesso!" ]);
            }
            else
                throw new Exception("Não foi possível gravar presença!");
        }
        catch (Exception $e)
        {
            LogEngine::writeErrorLog("Ao marcar presença: {$e->getMessage()}");
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
    }
}