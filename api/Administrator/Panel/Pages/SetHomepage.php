<?php
namespace VictorOpusculo\Parlaflix\Api\Administrator\Panel\Pages;

use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Pages\Page;
use VictorOpusculo\Parlaflix\Lib\Model\Settings\HomePageId;
use VictorOpusculo\PComp\RouteHandler;
use VOpus\PhpOrm\Exceptions\DatabaseEntityNotFound;

require_once __DIR__ . '/../../../../lib/Middlewares/AdminLoginCheck.php';
require_once __DIR__ . '/../../../../lib/Middlewares/JsonBodyParser.php';

final class SetHomepage extends RouteHandler
{
    public function __construct()
    {
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\adminLoginCheck';
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\jsonParser';
    }

    protected function PUT(): void
    {
        $conn = Connection::get();
        try
        {
            $id = $_POST['data']['page_id'] ?? null;
            $remove = $_POST['data']['remove'] ?? false;

            $page = new Page([ 'id' => $id ]);

            if (!$page->exists($conn) && !$remove)
                throw new DatabaseEntityNotFound("Página especificada não existe!", "pages");

            $setting = (new HomePageId([ 'value' => $id && !$remove ? $id : null ]));
            $result = $setting->save($conn);
            if ($result['affectedRows'] > 0)
            {
                LogEngine::writeLog("Homepage alterada! Página ID: {$id}");
                $this->json([ 'success' => 'Página inicial alterada com sucesso!' ]);
            }
            else
                $this->json([ 'info' => 'Nada alterado.' ]);
        }
        catch (\Exception $e)
        {
            LogEngine::writeErrorLog($e->getMessage());
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
    }
}