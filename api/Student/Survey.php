<?php
namespace VictorOpusculo\Parlaflix\Api\Student;

use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\PComp\RouteHandler;

require_once __DIR__ . '/../../lib/Middlewares/StudentLoginCheck.php';
require_once __DIR__ . '/../../lib/Middlewares/JsonBodyParser.php';

final class Survey extends RouteHandler
{
    public function __construct()
    {
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\studentLoginCheck';
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\jsonParser';
    }

    protected function POST(): void
    {
        $conn = Connection::get();
    }
}