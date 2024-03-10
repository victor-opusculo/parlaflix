<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Certificates;

use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class Home extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Certificados emitidos";
    }

    protected function markup(): Component|array|null
    {
        return component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text("Certificados emitidos")),
            tag('div', children:
            [
                tag('a', class: 'btn', href: URLGenerator::generatePageUrl('/admin/panel/certificates/set_bg_image'), children: text("Alterar imagem de fundo"))
            ])
        ]);
    }
}