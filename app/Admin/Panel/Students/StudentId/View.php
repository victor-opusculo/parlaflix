<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Students\StudentId;

use DateTimeZone;
use VictorOpusculo\Parlaflix\Components\Data\DataGrid;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Components\Panels\ConvenienceLinks;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Student;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class View extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Visualizar estudante";

        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->studentId))
                throw new \Exception("ID inválido!");

            $this->student = (new Student([ 'id' => $this->studentId ]))
            ->setCryptKey(Connection::getCryptoKey())
            ->getSingle($conn)
            ->fetchSubscriptionsWithProgressData($conn);

            $this->subscriptions = Data::transformDataRows($this->student->subscriptions,
            [
                'ID' => fn($s) => $s->id->unwrapOr(0),
                'Curso' => fn($s) => $s->course->name->unwrapOr(''),
                'Inscrição em' => fn($s) => $s->datetime->unwrapOr(false) 
                    ? date_create($s->datetime->unwrap(), new DateTimeZone($_SESSION['user_timezone'] ?? 'America/Sao_Paulo'))->format('d/m/Y H:i:s')
                    : '',
                'Aulas completadas' => fn($s) => (string)($s->getOtherProperties()->doneLessonCount ?? 0) . "/" . (string)($s->getOtherProperties()->lessonCount ?? 1) . ' (' .
                    number_format(($s->getOtherProperties()->doneLessonCount ?? 0) / ($s->getOtherProperties()->lessonCount ?? 1) * 100, 0) . '%)'
            ]);
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }

    }

    protected $studentId;
    private ?Student $student;
    private array $subscriptions = [];

    protected function markup(): Component|array|null
    {
        return isset($this->student) ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text("Visualizar estudante")),

            component(Label::class, labelBold: true, label: "ID", children: text($this->student->id->unwrapOr(''))),
            component(Label::class, labelBold: true, label: "Nome completo", children: text($this->student->full_name->unwrapOr(''))),
            component(Label::class, labelBold: true, label: "E-mail", children: text($this->student->email->unwrapOr(''))),

            component(Label::class, labelBold: true, label: "Telefone", children: text($this->student->other_data->unwrap()->telephone->unwrapOr(''))),
            component(Label::class, labelBold: true, label: "Instituição", children: text($this->student->other_data->unwrap()->institution->unwrapOr(''))),
            component(Label::class, labelBold: true, label: "Cargo", children: text($this->student->other_data->unwrap()->instRole->unwrapOr(''))),

            component(Label::class, labelBold: true, label: "Fuso horário", children: text($this->student->timezone->unwrapOr(''))),
            component(Label::class, labelBold: true, label: "Associado da ABEL?", children: text($this->student->is_abel_member->unwrapOr(0) ? "Sim" : "Não")),

            component(Label::class, labelBold: true, label: 'Termo LGPD Versão', children: text($this->student->lgpd_term_version->unwrapOr(''))),
            component(Label::class, labelBold: true, lineBreak: true, label: 'Termo LGPD', children:
                tag('textarea', rows: 6, class: 'w-full', readonly: true, children: text($this->student->lgpd_term->unwrapOr('')))
            ),

            component(ConvenienceLinks::class,
                editUrl: URLGenerator::generatePageUrl("/admin/panel/students/{$this->student->id->unwrapOr(0)}/edit"),
                deleteUrl: URLGenerator::generatePageUrl("/admin/panel/students/{$this->student->id->unwrapOr(0)}/delete")
            ),

            tag('h2', children: text("Inscrições")),
            tag('p', children: text("Total de inscrições deste estudante: " . count($this->subscriptions))),
            component(DataGrid::class,
                dataRows: $this->subscriptions,
                rudButtonsFunctionParamName: 'ID',
                detailsButtonURL: URLGenerator::generatePageUrl("/admin/panel/subscriptions/{param}")
            )

        ])
        : null;
    }
}