<?php
namespace VictorOpusculo\Parlaflix\Lib\Model\Tests;


class TestData
{
    /** @var TestQuestion[] */
    public array $questions = [];

    public function setQuestions(array $quests) : self { $this->questions = $quests; return $this; }

    public function toJson() : string
    {
        return json_encode($this);
    }

    public static function build(array $testDataFromJson) : self
    {
        $new = new self();
        $new->questions = array_map([TestQuestion::class, 'build'], $testDataFromJson['questions'] ?? []);

        return $new;
    }

    public static function buildFromJson(string $json) : self
    {
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        return self::build($decoded);
    }
}