<?php
namespace VictorOpusculo\Parlaflix\Components\Site;

use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Model\Pages\Page;
use VictorOpusculo\PComp\Component;

use function VictorOpusculo\PComp\Prelude\rawText;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

class PageViewer extends Component
{
    protected function setUp()
    {
    }

    public Page $page;

    protected function markup(): Component|array|null
    {
        return 
        [
            tag('h2', children: text($this->page->title->unwrapOr(''))),
            tag('div', class: 'mt-4', children: 
                $this->page->html_enabled->unwrapOr(0) ? 
                rawText($this->page->content->unwrapOr('')) :
                rawText(nl2br(Data::hsc($this->page->content->unwrapOr(''))))
            )
        ];
    }
} 