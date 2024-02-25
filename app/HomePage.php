<?php

namespace VictorOpusculo\Parlaflix\App;

use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class HomePage extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Parlaflix";    
    }

    protected function markup(): Component|array|null
    {
        return tag('h1', children: text('Página inicial Parlaflix!'));
    }
}