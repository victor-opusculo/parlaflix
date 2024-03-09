<?php
namespace VictorOpusculo\Parlaflix\App\Student\Panel;

use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Components\Panels\ButtonsContainer;
use VictorOpusculo\Parlaflix\Components\Panels\FeatureButton;
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
        HeadManager::$title = "Área do estudante";
    }

    protected function markup(): Component|array|null
    {
        return component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text('Área do estudante')),

            component(ButtonsContainer::class, children:
            [
                component(FeatureButton::class,
                    url: URLGenerator::generatePageUrl('/student/panel/subscription'),
                    label: 'Minhas inscrições',
                    iconUrl: URLGenerator::generateFileUrl('assets/pics/course.png'),
                    additionalClasses: 'bg-violet-700/50',
                    invertIconColorsOnDark: true
                )
            ])
        ]);
    }
}