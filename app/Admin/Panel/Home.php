<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel;

use VictorOpusculo\PComp\Component;

use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class Home extends Component
{
    protected function markup(): Component|array|null
    {
        return tag('h1', children: text('Painel de administração'));
    }
}