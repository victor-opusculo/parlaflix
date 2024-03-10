<?php
namespace VictorOpusculo\Parlaflix\Components\Site;

use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\PComp\Component;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\scTag;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

class CategoryGrid extends Component
{
    protected array $categories = [];

    protected function markup(): Component|array|null
    {
        return tag('div', class: 'p-4 flex md:flex-row flex-col flex-wrap items-center justify-center', children:
            count($this->categories) > 0
                ? array_map(fn($c) => tag('a', class: 'block overflow-auto relative p-2 mx-4 mb-4 h-[300px] min-w-[300px] max-w-[400px] rounded border border-neutral-300 dark:border-neutral-700 hover:brightness-75', 
                href: $c->id->unwrapOr(false)
                    ? URLGenerator::generatePageUrl("/info/course", [ 'category_id' => $c->id->unwrap() ])
                    : URLGenerator::generatePageUrl("/info/course"),
                children:
                [
                    tag('div', class: 'absolute w-full left-0 right-0 top-0 bottom-0', children:
                        isset($c->icon) 
                            ? scTag('img', class: 'absolute m-auto left-0 right-0 top-0 bottom-0', src: URLGenerator::generateFileUrl($c->icon->fileNameFromBaseDir()))
                            : scTag('img', class: 'absolute m-auto left-0 right-0 top-0 bottom-0', src: URLGenerator::generateFileUrl('assets/pics/barelogo.png'))
                    ),
                    tag('div', class: 'absolute bottom-0 left-0 right-0 z-10 dark:bg-neutral-700/50 bg-neutral-300/80 p-2 text-center', children: 
                    [
                        tag('div', children: text($c->title->unwrapOr(''))),
                        tag('div', class: 'flex flex-row items-center justify-center' , children: 
                        [
                            tag('span', children: text($c->getOtherProperties()->coursesNumber > 1 ? ($c->getOtherProperties()->coursesNumber ?? '') . ' cursos' : ($c->getOtherProperties()->coursesNumber ?? 0) . ' curso'))
                        ])
                    ])
                ]), $this->categories)
                : text("Não há categorias de cursos disponíveis.")
        );
    }
}