<?php
namespace VictorOpusculo\Parlaflix\App\Student\Panel;

use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Settings\LgpdTermText;
use VictorOpusculo\Parlaflix\Lib\Model\Settings\LgpdTermVersion;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Student;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;
use VictorOpusculo\PComp\ScriptManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class EditProfile extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Editar perfil";
        $conn = Connection::get();
        try
        {
            $this->lgpdTermText = (new LgpdTermText)->getSingle($conn)->value->unwrapOrElse(fn() => throw new \Exception("Não foi possível carregar termo LGPD"));
            $this->lgpdTermVersion = (new LgpdTermVersion)->getSingle($conn)->value->unwrapOrElse(fn() => throw new \Exception("Não foi possível carregar versão do termo LGPD."));
            $this->student = (new Student([ 'id' => $_SESSION['user_id'] ]))->setCryptKey(Connection::getCryptoKey())->getSingle($conn);

            ScriptManager::registerScript("timeZonesScript", Data::getTimeZonesToJavascript());
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    private string $lgpdTermText = '';
    private int $lgpdTermVersion = 0;
    private ?Student $student = null;

    protected function markup(): Component|array|null
    {
        return isset($this->student) ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text('Editar perfil')),
            tag('student-change-data-form', ...$this->student->getValuesForHtmlForm(skip: [ 'id', 'lgpdTermText', 'lgpdtermversion' ]), 
            studentid: $this->student->id->unwrapOr(0),
            lgpdtermversion: $this->lgpdTermVersion,
            children:
            [
                tag('textarea', class: 'w-full min-h-[calc(100vh-180px)]', readonly: true, name: 'lgpdTerm', children: text($this->lgpdTermText))
            ])

        ]) 
        : null;
    }
}