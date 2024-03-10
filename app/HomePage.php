<?php

namespace VictorOpusculo\Parlaflix\App;

use VictorOpusculo\Parlaflix\Components\Site\PageViewer;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Pages\Page;
use VictorOpusculo\Parlaflix\Lib\Model\Settings\HomePageId;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class HomePage extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Parlaflix";    
        $conn = Connection::get();
        try
        {
            $homePageIdSett = (new HomePageId)->getSingle($conn);
            $this->homePage = (new Page([ 'id' => $homePageIdSett->value->unwrap() ]))->getSingle($conn);

            HeadManager::$title = $this->homePage->title->unwrapOr('Parlaflix');
        }
        catch (\Exception $e)
        {}

    }

    private ?Page $homePage = null;

    protected function markup(): Component|array|null
    {
        return isset($this->homePage) && $this->homePage->is_published->unwrapOr(false)
            ? component(PageViewer::class, page: $this->homePage, showTitle: false)
            : tag('h1', children: text('Bem-vindo(a) ao Parlaflix!'));
    }
}