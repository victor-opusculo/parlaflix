<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel;

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
        HeadManager::$title = "Painel de Administração";    
    }

    protected function markup(): Component|array|null
    {
        return 
        [
            tag('h1', children: text('Painel de administração')),
            component(ButtonsContainer::class, children:
            [
                component(FeatureButton::class,
                    url: URLGenerator::generatePageUrl('/admin/panel/media'),
                    label: 'Mídias',
                    iconUrl: URLGenerator::generateFileUrl('assets/pics/media.png'),
                    additionalClasses: 'bg-violet-700/50',
                    invertIconColorsOnDark: true
                ),
                component(FeatureButton::class,
                    url: URLGenerator::generatePageUrl('/admin/panel/pages'),
                    label: 'Páginas',
                    iconUrl: URLGenerator::generateFileUrl('assets/pics/page.png'),
                    additionalClasses: 'bg-violet-700/50',
                    invertIconColorsOnDark: true
                ),
                component(FeatureButton::class,
                    url: URLGenerator::generatePageUrl('/admin/panel/courses'),
                    label: 'Cursos',
                    iconUrl: URLGenerator::generateFileUrl('assets/pics/course.png'),
                    additionalClasses: 'bg-violet-700/50',
                    invertIconColorsOnDark: true
                ),
                component(FeatureButton::class,
                    url: URLGenerator::generatePageUrl('/admin/panel/categories'),
                    label: 'Categorias',
                    iconUrl: URLGenerator::generateFileUrl('assets/pics/category.svg'),
                    additionalClasses: 'bg-violet-700/50',
                    invertIconColorsOnDark: true
                ),
                component(FeatureButton::class,
                    url: URLGenerator::generatePageUrl('/admin/panel/certificates'),
                    label: 'Certificados',
                    iconUrl: URLGenerator::generateFileUrl('assets/pics/certificate.svg'),
                    additionalClasses: 'bg-violet-700/50',
                    invertIconColorsOnDark: true
                ),
                component(FeatureButton::class,
                    url: URLGenerator::generatePageUrl('/admin/panel/students'),
                    label: 'Estudantes',
                    iconUrl: URLGenerator::generateFileUrl('assets/pics/student.png'),
                    additionalClasses: 'bg-violet-700/50',
                    invertIconColorsOnDark: true
                ),
                component(FeatureButton::class,
                    url: URLGenerator::generatePageUrl('/admin/panel/subscriptions'),
                    label: 'Inscrições',
                    iconUrl: URLGenerator::generateFileUrl('assets/pics/subscription.svg'),
                    additionalClasses: 'bg-violet-700/50',
                    invertIconColorsOnDark: true
                ),
                component(FeatureButton::class,
                    url: URLGenerator::generatePageUrl('/admin/panel/settings'),
                    label: 'Configurações',
                    iconUrl: URLGenerator::generateFileUrl('assets/pics/gear.svg'),
                    additionalClasses: 'bg-violet-700/50',
                    invertIconColorsOnDark: true
                )
            ])
        ];
    }
}