<?php
namespace VictorOpusculo\Parlaflix\Lib\Helpers;

use DateTimeZone;
use VOpus\PhpOrm\Option;
use VOpus\PhpOrm\Some;

final class Data
{
    public function __construct() { }

    public static function getMailConfigs()
    {
        $configs = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/../parlaflix_config.ini", true);
        return $configs['regularmail'];
    }

    public static function truncateText(?string $string, int $maxLength) : string
    {
        if (!$string) return '';
        return mb_strlen($string) > $maxLength ? mb_substr($string, 0, $maxLength) . '...' : $string;
    }

    public static function hsc(?string $string) : string
    {
        return htmlspecialchars($string ?? '', ENT_NOQUOTES);
    }

    public static function hscq(?string $string) : string
    {
        return htmlspecialchars($string ?? '', ENT_QUOTES);
    }

    public static function flattenArray(array $demo_array) : array
    {
        $new_array = array();
        array_walk_recursive($demo_array, function($array) use (&$new_array) { $new_array[] = $array; });
        return $new_array;
    }

    public static function transformDataRows(array $input, array $rules) : array
    {
        $output = [];
        
        if ($input)
            foreach ($input as $row)
            {
                $newRow = [];
                foreach ($rules as $newKeyName => $ruleFunction)
                    $newRow[$newKeyName] = $ruleFunction($row);

                $output[] = $newRow;
            }
            
        return $output;
    }

    public static function formatNameCase(?string $string) : string
    {
        if (!$string)
            return '';

        return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
    }

    public static function booleanTransformer(Option $val) : Some
    {
        $checked = $val->unwrapOr(0) ? 1 : 0;
        return Option::some($checked);
    }

    public static function getTimeZonesToJavascript() : string
    {
        return "
            Parlaflix.Time ??= {};
            Parlaflix.Time.TimeZones = [" . array_reduce(DateTimeZone::listIdentifiers(), fn($prev, $dtz) => ($prev ? $prev . ',' : '') . "\"$dtz\"" ) . "];
        ";
    }

    public static function formatCourseHourNumber(int|float $hours) : string
    {
        $rounded = round($hours, 2);
        $formatted = number_format($rounded, 2, ",", ".");
        return preg_replace("/,?0+$/", "", $formatted);
    }

    public static function firstName(?string $fullName) : string
    {
        $original = $fullName ?? 0;
        $names = explode(" ", $original);
        return array_shift($names) ?? "***";
    }
} 