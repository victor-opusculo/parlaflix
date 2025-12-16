<?php
namespace VictorOpusculo\Parlaflix\Lib\Model\Courses;

enum PresenceMethod: string
{
    case None = 'none';
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

    public static function satisfiesNone(string $val) : bool
    {
        return match ($val)
        {
            self::None->value => true,
            default => false
        };
    }
}