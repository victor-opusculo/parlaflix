<?php
namespace VictorOpusculo\Parlaflix\Api\Administrator\Panel\Surveys;

use Exception;
use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Survey;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\PComp\RouteHandler;

require_once __DIR__ . '/../../../../lib/Middlewares/AdminLoginCheck.php';
require_once __DIR__ . '/../../../../lib/Middlewares/JsonBodyParser.php';

final class SurveyId extends RouteHandler
{
    public function __construct()
    {
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\adminLoginCheck';
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\jsonParser';
    }

    protected $surveyId;

    protected function DELETE(): void
    {
        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->surveyId))
                throw new Exception("ID inválido!");

            $survey = (new Survey([ 'id' => $this->surveyId ]))->getSingle($conn);
            $result = $survey->delete($conn);

            if ($result['affectedRows'] > 0)
            {
                LogEngine::writeLog("Opinião excluída! ID: {$survey->id->unwrapOr(0)}");
                $this->json([ 'success' => "Opinão excluída com sucesso!"]);
            }
            else
                throw new Exception("Não foi possível excluir a opinião!");
        }
        catch (Exception $e)
        {
            LogEngine::writeErrorLog("Ao excluir opinião: {$e->getMessage()}");
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
    }
}