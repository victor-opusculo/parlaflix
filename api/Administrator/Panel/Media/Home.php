<?php
namespace VictorOpusculo\Parlaflix\Api\Administrator\Panel\Media;

use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Media\Media;
use VictorOpusculo\PComp\RouteHandler;

require_once __DIR__ . '/../../../../lib/Middlewares/AdminLoginCheck.php';
require_once __DIR__ . '/../../../../lib/Middlewares/JsonBodyParser.php';

final class Home extends RouteHandler
{
    public function __construct()
    {
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\adminLoginCheck';
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\jsonParser';
    }

    protected function GET(): void
    {
        $page = $_GET['page_num'] ?? 1;
        $resultsOnPage = $_GET['results_on_page'] ?? 20;
        $searchKeywords = $_GET['q'] ?? '';
        $orderBy = $_GET['order_by'] ?? '';
        $count = 0;

        $conn = Connection::get();
        try
        {
            $mediaGetter = new Media();
            $count = $mediaGetter->getCount($conn, $searchKeywords);
            $medias = $mediaGetter->getMultiple($conn, $searchKeywords, $orderBy, $page, $resultsOnPage);

            $this->json([ 'success' => '...', 'data' => [ 'dataRows' => $medias, 'allCount' => $count ] ]);
        }
        catch (\Exception $e)
        {
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
    }
}