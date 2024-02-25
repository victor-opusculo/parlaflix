<?php

namespace VictorOpusculo\Parlaflix;

use VictorOpusculo\PComp\{ApiInitializer};

require_once "vendor/autoload.php";

$api = new ApiInitializer(require_once __DIR__ . '/api/router.php');