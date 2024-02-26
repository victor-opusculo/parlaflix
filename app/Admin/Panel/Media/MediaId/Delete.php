<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Media\MediaId;

use Exception;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Media\Media;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\rawText;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class Delete extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Excluir mídia";

        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->mediaId))
                throw new \Exception("ID inválido!");

            $this->media = (new Media([ 'id' => $this->mediaId ]))->getSingle($conn);
        }
        catch (Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    protected $mediaId;
    private ?Media $media = null;

    protected function markup(): Component|array|null
    {
        return $this->media ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text('Excluir mídia')),
            tag('delete-entity-form', 
                deletescripturl: URLGenerator::generateApiUrl("/administrator/panel/media/{$this->media->id->unwrapOr(0)}/delete"),
                gobacktourl: URLGenerator::generatePageUrl('/admin/panel/media'),
                children:
                [
                    component(Label::class, label: 'ID', labelBold: true, children: text($this->media->id->unwrapOr(0))),
                    component(Label::class, label: 'Nome', labelBold: true, children: text($this->media->name->unwrapOr(''))),
                    component(Label::class, label: 'Descrição', labelBold: true, lineBreak: true, children: rawText(nl2br(Data::hsc($this->media->description->unwrapOr(''))))),
                    component(Label::class, label: 'Extensão', labelBold: true, children: text($this->media->file_extension->unwrapOr(''))),
                ]
            )
        ]) : null;
    }
}