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
use VictorOpusculo\PComp\ScriptManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class Edit extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Editar estudante";

        ScriptManager::registerScript('timeZonesScript', Data::getTimeZonesToJavascript());

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
            tag('h1', children: text("Editar estudante")),
            tag('edit-student-form',
                ...$this->student->getValuesForHtmlForm(skip: [ 'lgpdtermversion', 'lgpdTermText' ]),
                is_abel_member: $this->student->is_abel_member->unwrapOr(0)
            )
        ])
        : null;
    }
}