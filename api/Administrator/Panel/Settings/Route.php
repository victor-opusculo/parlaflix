<?php
namespace VictorOpusculo\Parlaflix\Api\Administrator\Panel\Settings;

use Exception;
use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Settings\MainInboxMail;
use VictorOpusculo\PComp\RouteHandler;

require_once __DIR__ . '/../../../../lib/Middlewares/AdminLoginCheck.php';
require_once __DIR__ . '/../../../../lib/Middlewares/JsonBodyParser.php';

final class Route extends RouteHandler
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
            $data = $_POST['data'] ?? [];

            $mainEmail = $data['main_inbox_mail'] ?? "";

            $sett1 = new MainInboxMail([ 'value' => $mainEmail ]);

            $result = $sett1->save($conn);
            if ($result['affectedRows'] > 0)
            {
                LogEngine::writeLog("Configurações alteradas!");
                $this->json([ 'success' => "Dados alterado com sucesso!" ]);
            }
            else
            {
                $this->json([ 'info' => "Nenhum dado alterado." ]);
            }
        }
        catch (Exception $e)
        {
            LogEngine::writeErrorLog($e->getMessage());
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
    }
}