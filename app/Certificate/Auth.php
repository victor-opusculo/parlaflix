<?php
namespace VictorOpusculo\Parlaflix\App\Certificate;

use DateTime;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class Auth extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Verificar certificado";

        if (isset($_GET['code']))
            $this->code = $_GET['code'];

        if (isset($_GET['date']))
            $this->date = $_GET['date'];

        if (isset($_GET['time']))
            $this->time = $_GET['time'];
    }

    private ?int $code = null;
    private ?string $date = null; 
    private ?string $time = null; 

    protected function markup(): Component|array|null
    {
        return component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text("Verificar certificado")),
            tag('certificate-auth-form',
                code: $this->code ?? "",
                date: $this->date ?? "",
                time: $this->time ?? ""
            )
        ]);
    }
}