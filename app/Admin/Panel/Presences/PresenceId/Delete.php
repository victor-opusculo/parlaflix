<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Presences\PresenceId;

use Exception;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\StudentLessonPassword;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class Delete extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Excluir presença";
        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->presenceId))
                throw new Exception("ID inválido!");

            $pres = (new StudentLessonPassword([ 'id' => $this->presenceId ]))
            ->setCryptKey(Connection::getCryptoKey())
            ->getSingleWithInfo($conn);

            $this->pres = $pres;
        }
        catch (Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    protected $presenceId;
    private ?StudentLessonPassword $pres = null;

    protected function markup(): Component|array|null
    {
        return isset($this->pres) ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text("Excluir presença")),

            tag('delete-entity-form', 
                deletescripturl: URLGenerator::generateApiUrl("/administrator/panel/presences/{$this->pres->id->unwrapOr(0)}"),
                gobacktourl: "/admin/panel/lessons/{$this->pres->lesson_id->unwrapOr(0)}/presences",
                children:
                [
                    tag('p', class: 'text-center font-bold', children: text("Você realmente deseja excluir esta presença? Esta operação é irreversível!")),
                    tag('ext-label', labelBold: true, label: "Aula", children: text($this->pres->getOtherProperties()->lessonTitle ?? "***")),
                    tag('ext-label', labelBold: true, label: "Estudante", children: text($this->pres->getOtherProperties()->studentName ?? "***")),
                    tag('ext-label', labelBold: true, label: "E-mail", children: text($this->pres->getOtherProperties()->studentEmail ?? "***")),
                ]
            )
        ])
        : null;
    }
}
