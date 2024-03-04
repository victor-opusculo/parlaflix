<?php
namespace VictorOpusculo\Parlaflix\Api\Administrator\Panel\Courses;

use Exception;
use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\PComp\RouteHandler;
use VOpus\PhpOrm\Exceptions\DatabaseEntityNotFound;

require_once __DIR__ . '/../../../../lib/Middlewares/AdminLoginCheck.php';
require_once __DIR__ . '/../../../../lib/Middlewares/JsonBodyParser.php';


final class CourseId extends RouteHandler
{
    public function __construct()
    {
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\adminLoginCheck';
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\jsonParser';
    }

    protected $courseId;

    protected function PUT(): void
    {
        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->courseId))
                throw new Exception('ID inválido!');

            $course = (new Course([ 'id' => $this->courseId ]))->getSingle($conn);
            $course->fillPropertiesFromFormInput($_POST['data'] ?? []);

            $result = $course->save($conn);
            if ($result['affectedRows'] > 0)
            {
                $this->json([ 'success' => 'Curso editado com sucesso!' ]);
                LogEngine::writeLog("Curso editado! ID: {$course->id->unwrapOr(0)}");
            }
            else
                $this->json([ 'info' => 'Nenhum dado alterado!' ]);
        }
        catch (DatabaseEntityNotFound $e)
        {
            LogEngine::writeErrorLog("Entidade não localizada ao editar curso (ID: {$this->courseId}) - " . $e->getMessage());
            $this->json([ 'error' => $e->getMessage() ], 404);
        }
        catch (\Exception $e)
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
            if (!Connection::isId($this->courseId))
                throw new Exception('ID inválido!');

            $course = (new Course([ 'id' => $this->courseId ]))->getSingle($conn);

            $result = $course->delete($conn);
            if ($result['affectedRows'] > 0)
            {
                $this->json([ 'success' => 'Curso excluído com sucesso!' ]);
                LogEngine::writeLog("Curso excluído! ID: {$course->id->unwrapOr(0)}");
            }
            else
                throw new Exception("Curso não excluído!");
        }
        catch (DatabaseEntityNotFound $e)
        {
            LogEngine::writeErrorLog("Entidade não localizada ao excluir curso (ID: {$this->courseId}) - " . $e->getMessage());
            $this->json([ 'error' => $e->getMessage() ], 404);
        }
        catch (\Exception $e)
        {
            LogEngine::writeErrorLog($e->getMessage());
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
    }
}