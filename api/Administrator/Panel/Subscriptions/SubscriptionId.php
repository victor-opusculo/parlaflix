<?php
namespace VictorOpusculo\Parlaflix\Api\Administrator\Panel\Subscriptions;

use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Subscription;
use VictorOpusculo\PComp\RouteHandler;

require_once __DIR__ . '/../../../../lib/Middlewares/AdminLoginCheck.php';
require_once __DIR__ . '/../../../../lib/Middlewares/JsonBodyParser.php';

class SubscriptionId extends RouteHandler
{
    public function __construct()
    {
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\adminLoginCheck';
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\jsonParser';
    }

    protected $subscriptionId;

    protected function DELETE(): void
    {
        $conn = Connection::get();
        try
        {
            $subs = (new Subscription([ 'id' => $this->subscriptionId ]))->getSingle($conn);

            $result = $subs->delete($conn);
            if ($result['affectedRows'] > 0)
            {
                LogEngine::writeLog("Inscrição excluída pela administração. Inscrição ID: " . $this->subscriptionId);
                $this->json([ 'success' => "Inscrição excluída com sucesso!" ]);
            }
            else
                throw new \Exception("Não foi possível excluir a inscrição!");
        }
        catch (\Exception $e)
        {
            LogEngine::writeErrorLog($e->getMessage());
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
    }
} 