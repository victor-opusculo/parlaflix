<?php

use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\PComp\Rpc\RpcInitializer;

require_once "vendor/autoload.php";

$rpc = new RpcInitializer(require_once __DIR__ . '/app/router.php', "route", "call", URLGenerator::generateFunctionUrl("{route}", "{call}"));