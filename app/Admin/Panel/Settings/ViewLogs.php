<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Settings;

use VictorOpusculo\Parlaflix\Components\Data\BasicSearchInput;
use VictorOpusculo\Parlaflix\Components\Data\DataGrid;
use VictorOpusculo\Parlaflix\Components\Data\OrderByLinks;
use VictorOpusculo\Parlaflix\Components\Data\Paginator;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Settings\MainInboxMail;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Student;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class ViewLogs extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Ver logs do sistema";

        $logs = __DIR__ . "/../../../../log";
        $files = glob("$logs/*");

        $this->files = array_map(fn(string $fp) => basename($fp), $files);
    }
    
    private array $files = [];

    protected function markup(): Component|array|null
    {
        return component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text("Ver logs do sistema")),
            tag('admin-log-view', children: tag('span', name: 'files', children: text(implode("|", $this->files))))
        ]);
    }
}