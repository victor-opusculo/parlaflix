<?php
namespace VictorOpusculo\Parlaflix\Api\Course;

use Exception;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\PComp\RouteHandler;

final class GetCourses extends RouteHandler
{
    protected function GET() : void
    {
        $conn = Connection::get();
        try
        {
            $mode = $_GET['mode'] ?? "latest"; // "latest" or "most_subscriptions"
            $restriction = $_GET['restriction'] ?? "open";
            $pageNum = (int)$_GET['page_num'] ?? 1;
            $numResultsOnPage = (int)$_GET['num_results_on_page'] ?? 5;
            $courses = match($mode)
            {
                "latest" => (new Course)->getLatest($conn, $restriction, $pageNum, $numResultsOnPage),
                "most_subscriptions" => (new Course)->getMostSubscriptions($conn, $restriction, $pageNum, $numResultsOnPage),
                default => (new Course)->getLatest($conn, $restriction, $pageNum, $numResultsOnPage)
            };

            $output = array_map(function(Course $c) use ($conn)
            {
                $c->fetchCoverMedia($conn);
                $c->fetchAverageSurveyPoints($conn);

                $imageUrl = null;
                if (isset($c->coverMedia))
                    $imageUrl = URLGenerator::generateFileUrl($c->coverMedia->fileNameFromBaseDir());
                else
                    $imageUrl = URLGenerator::generateFileUrl("assets/pics/nopic.png");

                return 
                [ 
                    "id" => (int)$c->id->unwrapOr(0),
                    "name" => $c->name->unwrapOr("Sem nome"), 
                    "hours" => (float)$c->hours->unwrapOr(0), 
                    "imageUrl" => $imageUrl, 
                    "subscriptionNumber" => $c->getOtherProperties()->subscriptionNumber ?? 0,
                    "surveyPoints" => $c->surveysAveragePoints,
                    "isExternal" => $c->is_external->unwrapOr(0) ? true : false
                ];

            }, $courses);

            /*
            Mocks:
            
            if ($pageNum > 0)
            {
                $output = [ ...$output, ...array_map(fn() =>  
                [ 
                    "id" => (int)100,
                    "name" => "Teste", 
                    "hours" => (float)10, 
                    "imageUrl" => URLGenerator::generateFileUrl("assets/pics/nopic.png"), 
                    "subscriptionNumber" => 10,
                    "surveyPoints" => 3.2
                ], range(1, 5)) ];
            }*/

            $this->json([ 'data' => $output ], 200);
        }
        catch (Exception $e)
        {
            $this->json([ 'error' => $e->getMessage() ]);
        }
        finally
        {
            $conn->close();
        }
    }
}