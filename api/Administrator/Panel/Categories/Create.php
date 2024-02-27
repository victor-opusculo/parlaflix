<?php
namespace VictorOpusculo\Parlaflix\Api\Administrator\Panel\Categories;

use Exception;
use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Category;
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
            $cat = new Category();
            $cat->fillPropertiesFromFormInput($_POST['data']);
            $result = $cat->save($conn);
            if ($result['newId'])
            {
                $this->json([ 'success' => 'Categoria cadastrada com sucesso!', 'data' => [ 'newId' => $result['newId'] ] ]);
                LogEngine::writeLog("Categoria cadastrada! ID: $result[newId]");
            }
            else
                throw new Exception("Erro ao cadastrar categoria!");
        }
        catch (\Exception $e)
        {
            LogEngine::writeErrorLog($e->getMessage());
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
    }
}