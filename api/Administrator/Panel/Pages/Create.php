<?php
namespace VictorOpusculo\Parlaflix\Api\Administrator\Panel\Pages;

use Exception;
use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Pages\Page;
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
            $page = new Page();
            $page->fillPropertiesFromFormInput($_POST['data'] ?? []);
            $result = $page->save($conn);
            if ($result['newId'])
            {
                $this->json([ 'success' => 'Página cadastrada com sucesso!', 'data' => [ 'newId' => $result['newId'] ] ]);
                LogEngine::writeLog("Página cadastrada! ID: $result[newId]");
            }
            else
                throw new Exception("Erro ao cadastrar página!");
        }
        catch (\Exception $e)
        {
            LogEngine::writeErrorLog($e->getMessage());
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
    }
}