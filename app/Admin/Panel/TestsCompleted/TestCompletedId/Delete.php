<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\TestsCompleted\TestcompletedId;

use Exception;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestCompleted;
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
        HeadManager::$title = "Excluir questionário respondido";
        $conn = Connection::get();
        try
        {

            if (!Connection::isId($this->testCompletedId))
                throw new Exception("ID inválido!");

            $this->test = new TestCompleted([ 'id' => $this->testCompletedId ])
            ->getSingle($conn)
            ->fetchSubscription($conn)
            ->fetchLesson($conn);
        }
        catch (Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }
    
    protected $testCompletedId;
    private ?TestCompleted $test = null;

    protected function markup(): Component|array|null
    {
        return isset($this->test) ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text("Excluir questionário respondido"))
        ])
        : null;
    }
}