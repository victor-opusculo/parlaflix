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
            $courses = match($mode)
            {
                "latest" => (new Course)->getLatest($conn),
                "most_subscriptions" => (new Course)->getMostSubscriptions($conn),
                default => (new Course)->getLatest($conn)
            };

            $output = array_map(function(Course $c) use ($conn)
            {
                $c->fetchCoverMedia($conn);

                $imageUrl = null;
                if ($c->members_only->unwrapOr(0))
                    $imageUrl = URLGenerator::generateFileUrl("assets/pics/members_only.png");
                else if (isset($c->coverMedia))
                    $imageUrl = URLGenerator::generateFileUrl($c->coverMedia->fileNameFromBaseDir());
                else
                    $imageUrl = URLGenerator::generateFileUrl("assets/pics/nopic.png");

                return 
                [ 
                    "id" => (int)$c->id->unwrapOr(0),
                    "name" => $c->name->unwrapOr("Sem nome"), 
                    "hours" => (float)$c->hours->unwrapOr(0), 
                    "imageUrl" => $imageUrl, 
                    "subscriptionNumber" => $c->getOtherProperties()->subscriptionNumber ?? 0 
                ];

            }, $courses);

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