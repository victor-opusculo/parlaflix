<?php
namespace VictorOpusculo\Parlaflix\Api\Administrator\Panel\Categories;

use Exception;
use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Category;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\PComp\RouteHandler;
use VOpus\PhpOrm\Exceptions\DatabaseEntityNotFound;

require_once __DIR__ . '/../../../../lib/Middlewares/AdminLoginCheck.php';
require_once __DIR__ . '/../../../../lib/Middlewares/JsonBodyParser.php';


final class CatId extends RouteHandler
{
    public function __construct()
    {
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\adminLoginCheck';
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\jsonParser';
    }

    protected $categoryId;

    protected function PUT(): void
    {
        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->categoryId))
                throw new Exception('ID inválido!');

            $category = (new Category([ 'id' => $this->categoryId ]))->getSingle($conn);
            $category->fillPropertiesFromFormInput($_POST['data'] ?? []);

            $result = $category->save($conn);
            if ($result['affectedRows'] > 0)
            {
                $this->json([ 'success' => 'Categoria editada com sucesso!' ]);
                LogEngine::writeLog("Categoria editada! ID: {$category->id->unwrapOr(0)}");
            }
            else
                $this->json([ 'info' => 'Nenhum dado alterado!' ]);
        }
        catch (DatabaseEntityNotFound $e)
        {
            LogEngine::writeErrorLog("Categoria não localizada ao editar (ID: {$this->categoryId}) - " . $e->getMessage());
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
            if (!Connection::isId($this->categoryId))
                throw new Exception('ID inválido!');

            $category = (new Category([ 'id' => $this->categoryId ]))->getSingle($conn);

            $result = $category->delete($conn);
            if ($result['affectedRows'] > 0)
            {
                $this->json([ 'success' => 'Categoria excluída com sucesso!' ]);
                LogEngine::writeLog("Categoria excluída! ID: {$category->id->unwrapOr(0)}");
            }
            else
                throw new Exception("Categoria não excluída!");
        }
        catch (DatabaseEntityNotFound $e)
        {
            LogEngine::writeErrorLog("Categoria não localizada ao excluir (ID: {$this->categoryId}) - " . $e->getMessage());
            $this->json([ 'error' => $e->getMessage() ], 404);
        }
        catch (\Exception $e)
        {
            LogEngine::writeErrorLog($e->getMessage());
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
    }
}