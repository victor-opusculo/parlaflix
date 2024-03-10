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
    public bool $showTitle = true;

    protected function markup(): Component|array|null
    {
        return 
        [
            $this->showTitle ? tag('h2', class: 'mb-4', children: text($this->page->title->unwrapOr(''))) : null,
            tag('div', children: 
                $this->page->html_enabled->unwrapOr(0) ? 
                rawText($this->page->content->unwrapOr('')) :
                rawText(nl2br(Data::hsc($this->page->content->unwrapOr(''))))
            )
        ];
    }
} 