<?php
namespace VictorOpusculo\Parlaflix\Api\Administrator\Panel\Pages;

use Exception;
use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Pages\Page;
use VictorOpusculo\PComp\RouteHandler;
use VOpus\PhpOrm\Exceptions\DatabaseEntityNotFound;

require_once __DIR__ . '/../../../../lib/Middlewares/AdminLoginCheck.php';
require_once __DIR__ . '/../../../../lib/Middlewares/JsonBodyParser.php';


final class PageId extends RouteHandler
{
    public function __construct()
    {
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\adminLoginCheck';
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\jsonParser';
    }

    protected $pageId;

    protected function PUT(): void
    {
        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->pageId))
                throw new Exception('ID inválido!');

            $page = (new Page([ 'id' => $this->pageId ]))->getSingle($conn);
            $page->fillPropertiesFromFormInput($_POST['data'] ?? []);

            $result = $page->save($conn);
            if ($result['affectedRows'] > 0)
            {
                $this->json([ 'success' => 'Página editada com sucesso!' ]);
                LogEngine::writeLog("Página editada! ID: {$page->id->unwrapOr(0)}");
            }
            else
                $this->json([ 'info' => 'Nenhum dado alterado!' ]);
        }
        catch (DatabaseEntityNotFound $e)
        {
            LogEngine::writeErrorLog("Página não localizada ao editar (ID: {$this->pageId}) - " . $e->getMessage());
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
            if (!Connection::isId($this->pageId))
                throw new Exception('ID inválido!');

            $page = (new Page([ 'id' => $this->pageId ]))->getSingle($conn);

            $result = $page->delete($conn);
            if ($result['affectedRows'] > 0)
            {
                $this->json([ 'success' => 'Página excluída com sucesso!' ]);
                LogEngine::writeLog("Página excluída! ID: {$page->id->unwrapOr(0)}");
            }
            else
                throw new Exception("Página não excluída!");
        }
        catch (DatabaseEntityNotFound $e)
        {
            LogEngine::writeErrorLog("Página não localizada ao excluir (ID: {$this->pageId}) - " . $e->getMessage());
            $this->json([ 'error' => $e->getMessage() ], 404);
        }
        catch (\Exception $e)
        {
            LogEngine::writeErrorLog($e->getMessage());
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
    }
}