<?php
namespace VictorOpusculo\Parlaflix\Lib\Model\Tests;


class TestQuestionOption
{
    public ?int $pictureMediaId = null;
    public ?string $pictureMediaExt = null;

    public string $text = "";
    public ?bool $isCorrect = false;

    public ?bool $studentSelected = null;

    public function setText(string $text) : self { $this->text = $text; return $this; }
    public function setIsCorrect(?bool $correct) : self { $this->isCorrect = $correct; return $this; }
    public function setPictureId(?int $id) : self { $this->pictureMediaId = $id; return $this; }
    public function setPictureExt(?string $ext) : self { $this->pictureMediaExt = $ext; return $this; }
    public function setStudentSelected(?bool $sel) : self { $this->studentSelected = $sel; return $this; }

    public static function build(array $optionFromJson) : self
    {
        $new = new self();
        $new->pictureMediaId = $optionFromJson['pictureMediaId'] ?? null;
        $new->text = $optionFromJson['text'];
        $new->isCorrect = $optionFromJson['isCorrect'] ?? false;
        $new->studentSelected = $optionFromJson['studentSelected'] ?? null;

        return $new;
    }
}