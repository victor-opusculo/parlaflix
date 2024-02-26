<?php
namespace VictorOpusculo\Parlaflix\Api\Administrator\Panel\Media;

use Exception;
use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Media\Media;
use VictorOpusculo\PComp\RouteHandler;
use VOpus\PhpOrm\Exceptions\DatabaseEntityNotFound;

require_once __DIR__ . '/../../../../lib/Middlewares/AdminLoginCheck.php';

final class MediaId extends RouteHandler
{
    public function __construct()
    {
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\adminLoginCheck';
    }

    protected $mediaId;

    protected function POST(): void
    {
        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->mediaId))
                throw new Exception('ID inválido!');

            $media = (new Media([ 'id' => $this->mediaId ]))->getSingle($conn);
            $media->fillPropertiesFromFormInput($_POST, $_FILES);

            $result = $media->save($conn);
            if ($result['affectedRows'] > 0)
            {
                $this->json([ 'success' => 'Mídia editada com sucesso!' ]);
                LogEngine::writeLog("Mídia editada! ID: {$media->id->unwrapOr(0)}");
            }
            else
                $this->json([ 'info' => 'Nenhum dado alterado!' ]);
        }
        catch (DatabaseEntityNotFound $e)
        {
            LogEngine::writeErrorLog("Mídia não localizada ao editar (ID: {$this->mediaId}) - " . $e->getMessage());
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
            if (!Connection::isId($this->mediaId))
                throw new Exception('ID inválido!');

            $media = (new Media([ 'id' => $this->mediaId ]))->getSingle($conn);

            $result = $media->delete($conn);
            if ($result['affectedRows'] > 0)
            {
                $this->json([ 'success' => 'Mídia excluída com sucesso!' ]);
                LogEngine::writeLog("Mídia excluída! ID: {$media->id->unwrapOr(0)}");
            }
            else
                throw new Exception("Mídia não excluída!");
        }
        catch (DatabaseEntityNotFound $e)
        {
            LogEngine::writeErrorLog("Mídia não localizada ao excluir (ID: {$this->mediaId}) - " . $e->getMessage());
            $this->json([ 'error' => $e->getMessage() ], 404);
        }
        catch (\Exception $e)
        {
            LogEngine::writeErrorLog($e->getMessage());
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
    }
}