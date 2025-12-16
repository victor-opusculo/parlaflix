<?php
namespace VictorOpusculo\Parlaflix\Lib\Model\Tests;


class TestQuestionOption
{
    public ?int $pictureMediaId = null;
    public string $text = "";
    public bool $isCorrect = false;

    public function setText(string $text) : self { $this->text = $text; return $this; }
    public function setIsCorrect(bool $correct) : self { $this->isCorrect = $correct; return $this; }
    public function setPictureId(?int $id) : self { $this->pictureMediaId = $id; return $this; }

    public static function build(array $optionFromJson) : self
    {
        $new = new self();
        $new->pictureMediaId = $optionFromJson['pictureMediaId'] ?? null;
        $new->text = $optionFromJson['text'];
        $new->isCorrect = $optionFromJson['isCorrect'] ?? false;

        return $new;
    }
}