<?php
namespace VictorOpusculo\Parlaflix\Api\Administrator\Panel\Media;

use Exception;
use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Media\Media;
use VictorOpusculo\PComp\RouteHandler;

require_once __DIR__ . '/../../../../lib/Middlewares/AdminLoginCheck.php';

final class Create extends RouteHandler
{
    public function __construct()
    {
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\adminLoginCheck';
    }

    protected function POST(): void
    {
        $conn = Connection::get();
        try
        {
            $media = new Media();
            $media->fillPropertiesFromFormInput($_POST, $_FILES);
            $result = $media->save($conn);
            if ($result['newId'])
            {
                $this->json([ 'success' => 'Mídia cadastrada com sucesso!', 'data' => [ 'newId' => $result['newId'] ] ]);
                LogEngine::writeLog("Mídia cadastrada! ID: $result[newId]");
            }
            else
                throw new Exception("Erro ao cadastrar mídia!");
        }
        catch (\Exception $e)
        {
            LogEngine::writeErrorLog($e->getMessage());
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
    }
}