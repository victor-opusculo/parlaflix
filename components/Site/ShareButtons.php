<?php
namespace VictorOpusculo\Parlaflix\Components\Site;

use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\PComp\Component;

use function VictorOpusculo\PComp\Prelude\tag;

final class ShareButtons extends Component
{
    protected string $title;
    protected string $text;
    protected ?string $imageUrl;

    protected function markup(): Component|array|null
    {
        return tag('div', ...['data-url' => URLGenerator::getHttpProtocolName() . "://" . $_SERVER['SERVER_NAME']], class: 'shareon', children:
        [
            tag('a', ...[ 'data-title' => $this->title, 'data-text' => $this->text, 'data-hashtags' => "Parlaflix", 'data-media' => $this->imageUrl] , class: 'facebook', ),
            tag('a', ...[ 'data-title' => $this->title, 'data-text' => $this->text, 'data-media' => $this->imageUrl], class: 'linkedin'),
            tag('a', ...[ 'data-title' => $this->title, 'data-text' => $this->text, 'data-media' => $this->imageUrl], class: 'teams'),
            tag('a', ...[ 'data-title' => $this->title, 'data-text' => $this->text, 'data-media' => $this->imageUrl], class: 'telegram'),
            tag('a', ...[ 'data-title' => $this->title, 'data-text' => $this->text, 'data-media' => $this->imageUrl], class: 'twitter'),
            tag('a', ...[ 'data-title' => $this->title, 'data-text' => $this->text, 'data-media' => $this->imageUrl], class: 'whatsapp'),
            tag('a', class: 'copy-url'),
            tag('a', class: 'email')
        ]);
    }
}