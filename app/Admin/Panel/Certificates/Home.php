<?php
namespace VictorOpusculo\Parlaflix\App\Admin\Panel\Certificates;

use DateTimeZone;
use VictorOpusculo\Parlaflix\Components\Data\BasicSearchInput;
use VictorOpusculo\Parlaflix\Components\Data\DataGrid;
use VictorOpusculo\Parlaflix\Components\Data\OrderByLinks;
use VictorOpusculo\Parlaflix\Components\Data\Paginator;
use VictorOpusculo\Parlaflix\Components\Label;
use VictorOpusculo\Parlaflix\Components\Layout\DefaultPageFrame;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\GeneratedCertificate;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\PComp\Component;
use VictorOpusculo\PComp\Context;
use VictorOpusculo\PComp\HeadManager;

use function VictorOpusculo\PComp\Prelude\component;
use function VictorOpusculo\PComp\Prelude\scTag;
use function VictorOpusculo\PComp\Prelude\tag;
use function VictorOpusculo\PComp\Prelude\text;

final class Home extends Component
{
    protected function setUp()
    {
        HeadManager::$title = "Certificados emitidos";
        $conn = Connection::get();
        try
        {
            $getter = (new GeneratedCertificate)->setCryptKey(Connection::getCryptoKey());
            $this->certCount = $getter->getCount($conn, $_GET['q'] ?? '');
            $certificates = $getter->getMultiple($conn, $_GET['q'] ?? '', $_GET['order_by'] ?? '', $_GET['page_num'] ?? 1, self::NUM_RESULTS_ON_PAGE);
            $this->certificates = Data::transformDataRows($certificates,
            [
                'Código (ID)' => fn($c) => $c->id->unwrapOr(0),
                'Curso' => fn($c) => $c->getOtherProperties()->courseName ?? '',
                'Estudante' => fn($c) => $c->getOtherProperties()->studentName ?? '',
                'E-mail' => fn($c) => $c->getOtherProperties()->studentEmail ?? '',
                'Data de emissão (seu local)' => fn($c) => date_create($c->datetime->unwrapOr('now'), new DateTimeZone('UTC'))->setTimezone(new DateTimeZone($_SESSION['user_timezone']))->format('d/m/Y H:i:s')
            ]);
        }
        catch (\Exception $e)
        {
            Context::getRef('page_messages')[] = $e->getMessage();
        }
    }

    public const NUM_RESULTS_ON_PAGE = 20;
    private array $certificates = [];
    private int $certCount = 0;

    protected function markup(): Component|array|null
    {
        return component(DefaultPageFrame::class, children:
        [
            tag('h1', children: text("Certificados emitidos")),
            tag('div', children:
            [
                tag('a', class: 'btn', href: URLGenerator::generatePageUrl('/admin/panel/certificates/set_bg_image'), children: text("Alterar imagem de fundo")),
                scTag('br'),
                component(Label::class, labelBold: true, label: !empty($_GET['q']) ? "Número de certificados (pesquisa atual)" : "Número total de certificados", children: text($this->certCount))
            ]),
            component(BasicSearchInput::class),
            component(OrderByLinks::class, linksDefinitions: [ 'ID' => 'id', 'Data de emissão' => 'datetime', 'E-mail' => 'student_email', 'Estudante' => 'student_name' ]),
            component(DataGrid::class,
                dataRows: $this->certificates,
            ),
            component(Paginator::class, totalItems: $this->certCount, pageNum: $_GET['page_num'] ?? 1, numResultsOnPage: self::NUM_RESULTS_ON_PAGE)
        ]);
    }
}