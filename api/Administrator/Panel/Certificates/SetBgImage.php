<?php
namespace VictorOpusculo\Parlaflix\Api\Administrator\Panel\Certificates;

use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Media\Media;
use VictorOpusculo\Parlaflix\Lib\Model\Settings\CertificateBackground2MediaId;
use VictorOpusculo\Parlaflix\Lib\Model\Settings\CertificateBackgroundMediaId;
use VictorOpusculo\PComp\RouteHandler;
use VOpus\PhpOrm\Exceptions\DatabaseEntityNotFound;

require_once __DIR__ . '/../../../../lib/Middlewares/AdminLoginCheck.php';
require_once __DIR__ . '/../../../../lib/Middlewares/JsonBodyParser.php';

final class SetBgImage extends RouteHandler
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
            $id = $_POST['data']['media_id'] ?? null;
            $id2 = $_POST['data']['media2_id'] ?? null;

            $media = new Media([ 'id' => $id ]);

            if (!$media->exists($conn))
                throw new DatabaseEntityNotFound("Mídia especificada não existe!", "media");

            $setting = (new CertificateBackgroundMediaId([ 'value' => $id ? $id : null ]));
            $result = $setting->save($conn);

            $setting2 = (new CertificateBackground2MediaId([ 'value' => $id2 ? $id2 : null ]));
            $result2 = $setting2->save($conn);

            $chRows = $result['affectedRows'];
            $chRows += $result2['affectedRows'];

            if ($chRows > 0)
            {
                LogEngine::writeLog("Fundo de certificado alterado! Mídia ID: {$id}");
                $this->json([ 'success' => 'Fundo de certificado alterado com sucesso!' ]);
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