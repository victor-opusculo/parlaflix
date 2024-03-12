<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Subscriptions;

use DateTimeZone;
use VictorOpusculo\Parlaflix\Components\Data\BasicSearchInput;
use VictorOpusculo\Parlaflix\Components\Data\DataGrid;
use VictorOpusculo\Parlaflix\Components\Data\OrderByLinks;
use VictorOpusculo\Parlaflix\Components\Data\Paginator;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
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
        HeadManager::$title = "Inscrições";
        $conn = Connection::get();
        try
        {
            $getter = (new Subscription)->setCryptKey(Connection::getCryptoKey());
            $this->subsCount = $getter->getCount($conn, $_GET['q'] ?? '');
            $subscriptions = $getter->getMultiple($conn, $_GET['q'] ?? '', $_GET['order_by'] ?? '', $_GET['page_num'] ?? 1, self::NUM_RESULTS_ON_PAGE);
            
            foreach ($subscriptions as $s)
                $s->fetchCourse($conn);
            
            $this->subscriptions = Data::transformDataRows($subscriptions, 
            [
                'ID' => fn($s) => $s->id->unwrapOr(0),
                'Curso' => fn($s) => $s->course->name->unwrapOr(''),
                'Estudante' => fn($s) => $s->getOtherProperties()->studentName ?? '',
                'Aulas completadas' => fn($s) => (string)($s->getOtherProperties()->doneLessonCount ?? 0) . '/' . (string)($s->getOtherProperties()->lessonCount ?? 1),
                'Pontos/requerido' => fn($s) => (string)($s->getOtherProperties()->studentPoints ?? 0) . '/' . (string)($s->getOtherProperties()->maxPoints ?? 0),
                'Situação' => fn($s) => ($s->getOtherProperties()->studentPoints ?? 0) >= $s->course->min_points_required->unwrapOr(INF) ? 'Aprovado' : 'Não aprovado',
                'Data de inscrição' => fn($s) => $s->datetime->unwrapOr(false)
                    ? date_create($s->datetime->unwrap(), new DateTimeZone('UTC'))->setTimezone(new DateTimeZone($_SESSION['user_timezone'] ?? 'America/Sao_Paulo'))->format('d/m/Y H:i:s')
                    : ''
            ]);
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    public const NUM_RESULTS_ON_PAGE = 20;
    private array $subscriptions = [];
    private int $subsCount = 0;

    protected function markup(): Component|array|null
    {
        return component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text('Inscrições')),

            component(BasicSearchInput::class),
            component(OrderByLinks::class, linksDefinitions: [ 'ID' => 'id', 'Curso' => 'name', 'Data de inscrição' => 'datetime' ]),

            tag('div', class: 'my-4', children: 
                text($this->subsCount > 1 ? "{$this->subsCount} inscrições no total" : "{$this->subsCount} inscrição no total")
            ),

            component(DataGrid::class,
                dataRows: $this->subscriptions,
                rudButtonsFunctionParamName: 'ID',
                detailsButtonURL: URLGenerator::generatePageUrl('/admin/panel/subscriptions/{param}'),
                editButtonURL: URLGenerator::generatePageUrl('/admin/panel/subscriptions/{param}/edit'),
                deleteButtonURL: URLGenerator::generatePageUrl('/admin/panel/subscriptions/{param}/delete'),
            ),

            component(Paginator::class, totalItems: $this->subsCount, pageNum: $_GET['page_num'] ?? 1, numResultsOnPage: self::NUM_RESULTS_ON_PAGE)
        ]);
    }
}