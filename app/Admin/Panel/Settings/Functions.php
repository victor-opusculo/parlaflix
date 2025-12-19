<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Settings;

use VictorOpusculo\PComp\Rpc\BaseFunctionsClass;
use VictorOpusculo\PComp\Rpc\ReturnsContentType;

require_once __DIR__ . '/../../../../lib/Middlewares/AdminLoginCheck.php';

final class Functions extends BaseFunctionsClass
{
    protected array $middlewares = ['\VictorOpusculo\Parlaflix\Lib\Middlewares\adminLoginCheck'];

    #[ReturnsContentType('plain/text', 'text')]
    public function fetchLog(array $query) : string
    {
        [ 'file' => $file ] = $query;

        $logPath = __DIR__ . "/../../../../log";
        $fileName = "$logPath/$file";

        if (file_exists($fileName))
            return file_get_contents($fileName);
        else
            return "Arquivo de log não encontrado!";
    }
}
