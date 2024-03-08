<?php
namespace VictorOpusculo\Parlaflix\Api\Administrator\Panel\Courses;

use Exception;
use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\PComp\RouteHandler;

require_once __DIR__ . '/../../../../lib/Middlewares/AdminLoginCheck.php';
require_once __DIR__ . '/../../../../lib/Middlewares/JsonBodyParser.php';

final class Create extends RouteHandler
{
    public function __construct()
    {
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\adminLoginCheck';
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\jsonParser';
    }

    protected function POST(): void
    {
        $conn = Connection::get();
        try
        {
            $course = new Course;
            $course->informDateTimeZone($_SESSION['user_timezone']);
            $course->fillPropertiesFromFormInput($_POST['data'] ?? []);

            //$this->json([ 'info' => print_r($_POST['data'], true) ]);

            $result = $course->save($conn);
            if ($result['newId'])
            {
                LogEngine::writeLog('Curso criado. ID: ' . $result['newId']);
                $this->json([ 'success' => 'Curso criado com sucesso!', 'data' => [ 'newId' => $result['newId'] ] ]);
            }
            else
                throw new Exception('Não foi possível gravar o curso!');
        }
        catch (\Exception $e)
        {
            LogEngine::writeErrorLog($e->getMessage());
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
    }
}