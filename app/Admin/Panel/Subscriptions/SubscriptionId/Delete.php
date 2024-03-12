<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Subscriptions\SubscriptionId;

use VictorOpusculo\Parlaflix\Components\Data\DateTimeTranslator;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Subscription;
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
        HeadManager::$title = "Excluir inscrição";
        $conn = Connection::get();
        try
        {
            if (!Connection::isId($this->subscriptionId))
                throw new \Exception("ID inválido!");

            $this->subscription = (new Subscription([ 'id' => $this->subscriptionId ]))
            ->setCryptKey(Connection::getCryptoKey())
            ->getSingleWithProgressData($conn)
            ->fetchCourse($conn);
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    protected $subscriptionId;
    private ?Subscription $subscription = null;

    protected function markup(): Component|array|null
    {
        return isset($this->subscription) ? component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text('Excluir inscrição')),

            tag('delete-entity-form', 
                deletescripturl: URLGenerator::generateApiUrl("/administrator/panel/subscriptions/{$this->subscription->id->unwrapOr(0)}"),
                gobacktourl: "/admin/panel/subscriptions",
                children:
                [
                    component(Label::class, labelBold: true, label: 'ID', children: text($this->subscription->id->unwrapOr(0))),
                    component(Label::class, labelBold: true, label: 'Curso', children: text($this->subscription->course->name->unwrapOr(''))),
                    component(Label::class, labelBold: true, label: 'Estudante', children: text($this->subscription->getOtherProperties()->studentName ?? '')),
                    component(Label::class, labelBold: true, label: 'Data', children: component(DateTimeTranslator::class, utcDateTime: $this->subscription->datetime->unwrapOr('now')))
                ]
            )
        ])
        : null;
    }
}