<?php
namespace VictorOpusculo\Parlaflix\Components\Panels;

use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\PComp\Component;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

class StudentSubscriptionsGrid extends Component
{
    protected array $subscriptions = [];

    protected function markup(): Component|array|null
    {
        return tag('div', class: 'p-4 flex md:flex-row flex-col flex-wrap items-center justify-center', children:
            count($this->subscriptions) > 0
                ? array_map(fn($s) => component(StudentSubscriptionCard::class, subscription: $s, detailsUrl: URLGenerator::generatePageUrl("/student/panel/subscription/{$s->id->unwrapOr(0)}")), $this->subscriptions)
                : text(empty($_GET['q']) ? "Não há inscrições ainda. Inscreva-se em algum curso pelo menu de cursos acima." : "Não há resultados segundo os termos de pesquisa.")
        );
    }
}