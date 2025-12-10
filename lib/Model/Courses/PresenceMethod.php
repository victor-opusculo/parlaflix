<?php
namespace VictorOpusculo\Parlaflix\Lib\Model\Courses;

enum PresenceMethod: string
{
    case Password = 'password';
    case Test = 'test';
    case TestAndPassword = 'test_and_password';
}