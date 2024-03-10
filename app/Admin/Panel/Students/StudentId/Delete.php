<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Students\StudentId;

use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Student;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;
use VictorOpusculo\PComp\ScriptManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class Delete extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Excluir estudante";

        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->studentId))
                throw new \Exception("ID inválido!");

            $this->student = (new Student([ 'id' => $this->studentId ]))
            ->setCryptKey(Connection::getCryptoKey())
            ->getSingle($conn);

        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }

    }

    protected $studentId;
    private ?Student $student;

    protected function markup(): Component|array|null
    {
        return isset($this->student) ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text("Excluir estudante")),
            tag('delete-entity-form',
                deletescripturl: URLGenerator::generateApiUrl("/administrator/panel/students/{$this->student->id->unwrapOr(0)}"),
                gobacktourl: "/admin/panel/students",
                children:
                [
                    tag('p', class: 'text-center', children: text("Deseja realmente excluir este estudante? Esta ação não pode ser desfeita!")),

                    component(Label::class, labelBold: true, label: 'ID', children: text($this->student->id->unwrapOr(0))),
                    component(Label::class, labelBold: true, label: 'Nome completo', children: text($this->student->full_name->unwrapOr(''))),
                    component(Label::class, labelBold: true, label: 'E-mail', children: text($this->student->email->unwrapOr('')))
                ]
            ),
        ])
        : null;
    }
}