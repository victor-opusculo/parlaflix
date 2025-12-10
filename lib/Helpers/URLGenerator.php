<?php
namespace VictorOpusculo\Parlaflix\Lib\Helpers;

final class URLGenerator
{
	private function __construct() { }
	
	public static ?bool $useFriendlyUrls = null;
	public static ?string $baseUrl = null;
	
	public static function loadConfigs() : void
	{
		if (isset(self::$useFriendlyUrls) && isset(self::$baseUrl)) return;

		$configs = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/../parlaflix_config.ini", true);
		self::$useFriendlyUrls = (bool)($configs['urls']['usefriendly']);
		self::$baseUrl = $configs['urls']['baseurl'];
	}

	public static function generatePageUrl(string $pagePath, array $query = []) : string
	{
		self::loadConfigs();
		$qs = count($query) > 0 ? (self::$useFriendlyUrls ? '?' : '&') . self::generateQueryString($query) : '';
		return match (self::$useFriendlyUrls)
		{
			true => self::$baseUrl . ($pagePath[0] == '/' ? $pagePath . $qs : '/' . $pagePath . $qs),
			false => self::$baseUrl . "/index.php?page=$pagePath$qs"
		};
	}
	
	public static function generateFileUrl(string $filePathFromRoot, array $query = []) : string
	{
		self::loadConfigs();

		$qs = count($query) > 0 ? '?' . self::generateQueryString($query) : '';
		return match (self::$useFriendlyUrls)
		{
			true => self::$baseUrl . "/--file/$filePathFromRoot" . $qs,
			false => self::$baseUrl . ($filePathFromRoot[0] == '/' ? '/' . mb_substr($filePathFromRoot, 1) . $qs : '/' . $filePathFromRoot . $qs)
		};
	}
	
	public static function generateScriptUrl(string $filePathFromScriptDir, array $query = []) : string
	{
		self::loadConfigs();

		$qs = count($query) > 0 ? '?' . self::generateQueryString($query) : '';
		return match (self::$useFriendlyUrls)
		{
			true => self::$baseUrl . "/--script/$filePathFromScriptDir" . $qs,
			false => self::$baseUrl . ($filePathFromScriptDir[0] == '/' ? "/script$filePathFromScriptDir" . $qs : "/script/$filePathFromScriptDir" . $qs)
		};
	}

	public static function generateApiUrl(string $apiPath, array $query = []) : string
	{
		self::loadConfigs();
		$qs = count($query) > 0 ? (self::$useFriendlyUrls ? '?' : '&') . self::generateQueryString($query) : '';
		return match (self::$useFriendlyUrls)
		{
			true => self::$baseUrl . '/--api' . ($apiPath[0] == '/' ? $apiPath . $qs : '/' . $apiPath . $qs),
			false => self::$baseUrl . "/api.php?route=$apiPath$qs"
		};
	}

	public static function generateFunctionUrl(string $route, string $call = "", array $query = []) : string
	{
		self::loadConfigs();
		$qs = (self::$useFriendlyUrls ? '?' : '&') . ($call ? "call=" . $call : "") . (count($query) > 0 ? '&' . self::generateQueryString($query) : '');

		if ($qs[strlen($qs) - 1] === '&')
			$qs = substr($qs, 0, strlen($qs) - 1);

		return match (self::$useFriendlyUrls)
		{
			true => self::$baseUrl . '/--function' . ($route[0] == '/' ? ($route . $qs) : '/' . $route . $qs),
			false => self::$baseUrl . "/function.php?route=$route$qs"
		};
	}

	public static function getHttpProtocolName() : string
	{
		$isHttps = $_SERVER['HTTPS'] ?? $_SERVER['REQUEST_SCHEME'] ?? $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;
		$isHttps = $isHttps && (strcasecmp('on', $isHttps) == 0 || strcasecmp('https', $isHttps) == 0);
		return $isHttps ? 'https' : 'http';
	}

	private static function generateQueryString(array $queryData) : string
	{
		return http_build_query($queryData);
	}
}