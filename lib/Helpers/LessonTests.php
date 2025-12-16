<?php
namespace VictorOpusculo\Parlaflix\Lib\Helpers;

use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestData;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestQuestion;

final class LessonTests
{
    public static function calculateGrade(array $correctAndMaximum) : float
    {
        [$corr, $max] = $correctAndMaximum;
        return $corr / $max * 100;
    }

    public static function calculateCorrectAnswers(TestData $testData) : array
    {
        $correctQuestions = 0;
        foreach ($testData->questions as $qi => $quest)
        {
            $isQuestionCorrect = true;
            foreach ($quest->options as $oi => $opt)
            {

                $isQuestionCorrect = $isQuestionCorrect && ($opt->studentSelected === $opt->isCorrect);
            }

            if ($isQuestionCorrect) $correctQuestions++;
        }

        return [ $correctQuestions, count($testData->questions) ];
    }

    public static function setAndCalculateCorrectAnswers(TestData $skel, array $answers) : array
    {
        $correctQuestions = 0;
        foreach ($skel->questions as $qi => $quest)
        {
            $questAnsw = $answers[$qi];
            $isQuestionCorrect = true;
            foreach ($quest->options as $oi => $opt)
            {
                $opt->setStudentSelected($questAnsw[$oi]);
                $isQuestionCorrect = $isQuestionCorrect && ($opt->studentSelected === $opt->isCorrect);
            }

            if ($isQuestionCorrect) $correctQuestions++;
        }

        return [ $skel, $correctQuestions, count($skel->questions) ];
    }

    public static function questionResult(TestQuestion $quest) : bool
    {
        $isQuestionCorrect = true;
        foreach ($quest->options as $oi => $opt)
        {
            $isQuestionCorrect = $isQuestionCorrect && ($opt->studentSelected === $opt->isCorrect);
        }

        return $isQuestionCorrect;
    }
}