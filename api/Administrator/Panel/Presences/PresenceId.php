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

final class PresenceId extends RouteHandler
{
    public function __construct()
    {
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\adminLoginCheck';
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\jsonParser';
    }

    protected $presenceId;

    protected function DELETE(): void
    {
        $conn = Connection::get();
        try
        {
            $pres = (new StudentLessonPassword([ 'id' => $this->presenceId ]))->getSingle($conn);
            $result = $pres->delete($conn);
            if ($result['affectedRows'])
            {
                LogEngine::writeLog("Presença excluída! ID {$this->presenceId}, aula ID {$pres->lesson_id->unwrapOr(0)}");
                $this->json([ 'success' => "Presença excluída com sucesso!" ]);
            }
            else
                throw new Exception("Não foi possível excluir presença!");
        }
        catch (Exception $e)
        {
            LogEngine::writeErrorLog("Ao excluir presença: {$e->getMessage()}");
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
    }
}