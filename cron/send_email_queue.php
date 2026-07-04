<?php

use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Email\Queue;

require_once __DIR__ . "/../vendor/autoload.php";

$conn = Connection::getTest();

/** @var Queue[] */
$queue = (new Queue)->getAll($conn);

foreach ($queue as $item)
    $item->sendEmailCron();

new Queue()->clearAll($conn);