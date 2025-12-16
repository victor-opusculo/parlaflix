<?php
namespace VictorOpusculo\Parlaflix\Lib\Model\Tests;

class TestQuestion
{
    public ?int $pictureMediaId = null;
    public string $text = "";

    // Used only by student form
    public ?string $pictureMediaExt = null;
    public ?bool $mulipleAnswers = false;

    /** @var TestQuestionOption[] */
    public array $options = [];


    public function setText(string $text) : self { $this->text = $text; return $this; }
    public function setPictureId(?int $id) : self { $this->pictureMediaId = $id; return $this; }
    public function setPictureExt(?string $ext) : self { $this->pictureMediaExt = $ext; return $this; }
    public function setMulipleAnswers(?bool $mop) : self { $this->mulipleAnswers = $mop; return $this; }

    public function setOptions(array $opts) : self { $this->options = $opts; return $this; }

    public static function build(array $questionFromJson) : self
    {
        $new = new self();
        $new->pictureMediaId = $questionFromJson['pictureMediaId'] ?? null;
        $new->text = $questionFromJson['text'] ?? "";
        $new->options = array_map([TestQuestionOption::class, 'build'], $questionFromJson['options'] ?? []);
        $new->mulipleAnswers = array_reduce($new->options, fn(int $carry, TestQuestionOption $opt) => $carry + ($opt->isCorrect ? 1 : 0), 0) > 1;

        return $new;
    }
}