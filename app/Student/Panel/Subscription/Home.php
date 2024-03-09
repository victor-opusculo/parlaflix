<?php
namespace VictorOpusculo\Parlaflix\App\Student\Panel\Subscription;

use VictorOpusculo\Parlaflix\Components\Data\BasicSearchInput;
use VictorOpusculo\Parlaflix\Components\Data\OrderByLinks;
use VictorOpusculo\Parlaflix\Components\Data\Paginator;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Components\Panels\StudentSubscriptionsGrid;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Subscription;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class Home extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Minhas inscrições";
        $conn = Connection::get();
        try
        {
            $getter = (new Subscription([ 'student_id' => $_SESSION['user_id'] ?? 0 ]));
            $this->allSubscriptionCount = $getter->getCountFromStudent($conn, $_GET['q'] ?? '');
            $this->subscriptions = $getter->getMultipleFromStudent($conn, $_GET['q'] ?? '', $_GET['order_by'] ?? '', $_GET['page_num'] ?? 1, self::NUM_RESULTS_ON_PAGE);

            foreach ($this->subscriptions as $sub)
            {
                $sub->fetchCourse($conn);
                $sub->course
                ->informDateTimeZone($_SESSION['user_timezone'] ?? 'America/Sao_Paulo')
                ->fetchCoverMedia($conn);
            }
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    public const NUM_RESULTS_ON_PAGE = 15;
    private array $subscriptions = [];
    private int $allSubscriptionCount = 0;

    protected function markup(): Component|array|null
    {
        return component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text('Minhas inscrições')),

            component(BasicSearchInput::class),
            component(OrderByLinks::class, linksDefinitions: [ 'Nome do curso' => 'name', 'Data de inscrição' => 'datetime' ]),

            component(StudentSubscriptionsGrid::class, subscriptions: $this->subscriptions),
            component(Paginator::class, totalItems: $this->allSubscriptionCount, numPage: $_GET['page_num'] ?? 1, numResultsOnPage: self::NUM_RESULTS_ON_PAGE)
        ]);
    }
}