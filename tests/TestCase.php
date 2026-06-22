<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;

abstract class TestCase extends BaseTestCase
{
    public function getDatabaseConn() : \mysqli
    {
        return Connection::getTest();
    }

    public function getDatabaseCrypt() : string
    {
        return Connection::getCryptoKeyTest();
    }
}
