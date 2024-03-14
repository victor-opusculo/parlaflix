<?php
namespace VictorOpusculo\Parlaflix\App\Student;

use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class RecoverPassword extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Recuperar acesso de conta";

    }

    protected function markup(): Component|array|null
    {
        return component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text("Recuperar acesso")),
            tag('student-recover-password')
        ]);
    }
}