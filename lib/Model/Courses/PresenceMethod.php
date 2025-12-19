<?php
namespace VictorOpusculo\Parlaflix\Lib\Model\Courses;

enum PresenceMethod: string
{
    case Auto = 'auto';
    case Never = 'never';
    case Password = 'password';
    case Test = 'test';
    case TestAndPassword = 'test_and_password';

    public static function satisfiesPassword(string $val) : bool
    {
        return match ($val)
        {
            self::Password->value => true,
            self::TestAndPassword->value => true,
            default => false
        };
    }

    public static function satisfiesTest(string $val) : bool
    {
        return match ($val)
        {
            self::Test->value => true,
            self::TestAndPassword->value => true,
            default => false
        };
    }

    public static function satisfiesAuto(string $val) : bool
    {
        return match ($val)
        {
            self::Auto->value => true,
            default => false
        };
    }

    public static function satisfiesNever(string $val) : bool
    {
        return match ($val)
        {
            self::Never->value => true,
            "" => true,
            '$default' => true,
            default => false
        };
    }

    private static function sqlList(array $enumItems) : string
    {

        $listStr = array_map(fn(self|string $eni) => is_string($eni) ? $eni : $eni->value, $enumItems);

        return "'" . implode("','", $listStr) . "'";
    }

    public static function sqlListPassword() : string
    {
        return self::sqlList([ self::Password, self::TestAndPassword ]);
    }

    public static function sqlListTest() : string
    {
        return self::sqlList([ self::Test, self::TestAndPassword ]);
    }

    public static function sqlListAuto() : string
    {
        return self::sqlList([ self::Auto ]);
    }

    public static function sqlListNever() : string
    {
        return self::sqlList([ self::Never, "", '$default' ]);
    }
}