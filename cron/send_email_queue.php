<?php

use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Email\Queue;

require_once __DIR__ . "/../vendor/autoload.php";

const MAX_EMAILS_PER_CALL = 12;

$conn = Connection::getTest();

/** @var Queue[] */
$queue = (new Queue)->getPartial($conn, MAX_EMAILS_PER_CALL);

foreach ($queue as $item)
    $item->sendEmailCron();

$deletedRows = new Queue()->clearPartial($conn, MAX_EMAILS_PER_CALL);

echo $deletedRows === MAX_EMAILS_PER_CALL ? "1 $deletedRows" : "0 $deletedRows";
