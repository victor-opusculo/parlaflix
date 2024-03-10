<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Certificates;

use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Settings\CertificateBackgroundMediaId;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class SetBgImage extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Alterar imagem de fundo de certificados";
        $conn = Connection::get();
        try
        {
            $this->bgMediaIdSett = (new CertificateBackgroundMediaId)->getSingle($conn);
        }
        catch (\Exception $e) {}
    }

    private ?CertificateBackgroundMediaId $bgMediaIdSett = null;

    protected function markup(): Component|array|null
    {
        return component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text('Alterar fundo de certificados')),
            tag('set-certificate-bg-form', media_id: isset($this->bgMediaIdSett) ? $this->bgMediaIdSett->value->unwrapOr('') : '')
        ]);
    } 
}