<?php

namespace VictorOpusculo\Parlaflix\Api\Administrator;

use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\PComp\RouteHandler;

require_once __DIR__ . '/../../lib/Middlewares/JsonBodyParser.php';

final class Logout extends RouteHandler
{
    public function __construct()
    {
    }

    protected function GET(): void
    {
        session_name('parlaflix_admin_user');
        session_start();
        LogEngine::writeLog("Log-off de administrador realizado.");
        session_unset();
        if (isset($_SESSION)) session_destroy();
        $this->json([ 'success' => 'Você saiu!' ]);
        exit;
    }
}