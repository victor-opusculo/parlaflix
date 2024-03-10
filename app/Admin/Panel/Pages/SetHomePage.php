<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Pages;

use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Settings\HomePageId;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;
use VOpus\PhpOrm\Exceptions\DatabaseEntityNotFound;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class SetHomePage extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Definir página inicial";

        $conn = Connection::get();
        try
        {
            $this->homePageId = (new HomePageId)->getSingle($conn);
        }
        catch (DatabaseEntityNotFound $e) {}
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    private ?HomePageId $homePageId = null;

    protected function markup(): Component|array|null
    {
        return component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text("Definir página inicial")),
            tag('set-homepage-form', page_id: isset($this->homePageId) ? Data::hscq($this->homePageId->value->unwrapOr('')) : '')
        ]);
    }
}