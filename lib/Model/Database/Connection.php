<?php
namespace VictorOpusculo\Parlaflix\Lib\Model\Database;

use mysqli;

abstract class Connection
{
    private function __construct() { }

    public static ?mysqli $conn;

    public static function create() : mysqli
    {
        $configs = self::getDatabaseConfig(); 
        return self::createBase($configs);
    }

    public static function createTest() : mysqli
    {
        $configs = self::getDatabaseConfigTest(); 
        return self::createBase($configs);
    }

    private static function createBase(array $configs) : mysqli
    {
        $serverName = $configs['servername'];
		$userName = $configs['username'];
		$password = $configs['password'];
		$dbname = $configs['dbname'];

		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

		$conn = new \mysqli($serverName, $userName, $password, $dbname);
		if ($conn->connect_error)
		{
			die("Connection failed! " . $conn->connect_error);
		}
		
		//$conn->query("SET NAMES 'utf8';");
		$conn->set_charset('utf8mb4');
		
        self::$conn = $conn;
		return self::$conn;
    }

    public static function get() : mysqli
    {
        if (isset(self::$conn))
            return self::$conn;
        else
            return self::create();
    }

    public static function getTest() : mysqli
    {
        if (isset(self::$conn))
            return self::$conn;
        else
            return self::createTest();
    }

    public static function close()
    {
        if (isset(self::$conn)) self::$conn->close();
    }

    public static function getCryptoKey() : string
	{
		return self::getDatabaseConfig()['crypto'];
	}

    public static function getCryptoKeyTest() : string
	{
		return self::getDatabaseConfigTest()['crypto'];
	}

    private static function getDatabaseConfig() : array
	{
		$configs = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/../parlaflix_config.ini", true);
		return $configs['database'];
	}

    private static function getDatabaseConfigTest() : array
	{
		$configs = parse_ini_file(__DIR__ . "/../../../../parlaflix_config.ini", true);
		return $configs['database'];
	}

    public static function isId($param) : bool
    {
        return isset($param) && is_numeric($param) && $param > 0;
    }

}