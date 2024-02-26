<?php
namespace VictorOpusculo\Parlaflix\Components\Panels;

use VictorOpusculo\PComp\Component;
use function VictorOpusculo\PComp\Prelude\{ tag, text };

class DeleteEntityForm extends Component
{
    public function setUp() { }

    protected string $deleteScriptUrl;

    protected function markup(): Component|array|null
    {
        return tag('form', method: 'post', action: $this->deleteScriptUrl, children:
        [
            ...$this->children,
            tag('div', class: 'text-center my-4', children:
            [
                tag('button', type: 'submit', class: 'btn mr-4', children: [ text('Sim, excluir') ]),
                tag('button', type: 'button', class: 'btn', onclick: 'history.back();', children: [ text('Não excluir') ]),
            ])
        ]);
    }
}