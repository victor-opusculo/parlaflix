<?php
namespace VictorOpusculo\Parlaflix\Components\Site;

use VictorOpusculo\Parlaflix\Components\Site\CourseCard;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\PComp\Component;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

class CourseGrid extends Component
{
    protected array $courses = [];
    protected bool $isUserAbelMember = false;

    protected function markup(): Component|array|null
    {
        return tag('div', class: 'p-4 flex md:flex-row flex-col flex-wrap items-center justify-center', children:
            count($this->courses) > 0
                ? array_map(fn($c) => component(CourseCard::class, isUserAbelMember: $this->isUserAbelMember, course: $c, detailsUrl: URLGenerator::generatePageUrl("/info/course/{$c->id->unwrapOr(0)}")), $this->courses)
                : text("Não há cursos disponíveis.")
        );
    }
}