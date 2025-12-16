<?php
namespace VictorOpusculo\Parlaflix\Lib\Model\Tests;

class TestQuestion
{
    public ?int $pictureMediaId = null;
    public string $text = "";

    /** @var TestQuestionOption[] */
    public array $options = [];


    /** @var int[] */
    public ?array $studentAnswers = null;

    public function setText(string $text) : self { $this->text = $text; return $this; }
    public function setPictureId(?int $id) : self { $this->pictureMediaId = $id; return $this; }

    public function setOptions(array $opts) : self { $this->options = $opts; return $this; }
    public function setStudentAnswers(?array $answ) : self { $this->studentAnswers = $answ; return $this; }

    public static function build(array $questionFromJson) : self
    {
        $new = new self();
        $new->pictureMediaId = $questionFromJson['pictureMediaId'] ?? null;
        $new->text = $questionFromJson['text'] ?? "";
        $new->options = array_map([TestQuestionOption::class, 'build'], $questionFromJson['options'] ?? []);

        return $new;
    }
}