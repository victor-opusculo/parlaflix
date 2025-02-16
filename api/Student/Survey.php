<?php
namespace VictorOpusculo\Parlaflix\Api\Student;

use Exception;
use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Subscription;
use VictorOpusculo\PComp\RouteHandler;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Survey as CourseSurvey;

require_once __DIR__ . '/../../lib/Middlewares/StudentLoginCheck.php';
require_once __DIR__ . '/../../lib/Middlewares/JsonBodyParser.php';

final class Survey extends RouteHandler
{
    public function __construct()
    {
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\studentLoginCheck';
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\jsonParser';
    }

    protected function POST(): void
    {
        $conn = Connection::get();
        $subscriptionId = $_GET['subscription_id'] ?? 0;
        $studentId = $_SESSION['user_id'] ?? 0;
        try
        {
            $subscription = new Subscription([ 'id' => $subscriptionId, 'student_id' => $studentId ]);
            $subscription = $subscription
            ->getSingleFromStudent($conn)
            ->fetchCourse($conn);

            $isApproved = ($subscription->getOtherProperties()->studentPoints ?? 0) >= ($subscription->course->min_points_required->unwrapOr(INF));

            if (!$isApproved)
                throw new Exception("Você não foi aprovado(a) neste curso!");

            $newSurvey = new CourseSurvey([ 'course_id' => $subscription->course->id->unwrapOr(0), 'student_id' => $studentId ]);
            $existsSurvey = $newSurvey->existsFromStudentAndCourse($conn);

            if ($existsSurvey)
                throw new Exception("Você já enviou sua opinião para este curso!");

            $newSurvey->fillPropertiesFromFormInput($_POST['data'] ?? []);
            $result = $newSurvey->save($conn);
            if ($result['newId'])
            {
                LogEngine::writeLog("Opinião de estudante criada! ID: $result[newId]. Subscrição ID: $subscriptionId");
                $this->json([ 'success' => "Obrigado! Opinião salva com sucesso!" ]);
            }
            else
                throw new Exception("Não foi possível salvar a opinião!");
            
        }
        catch (Exception $e)
        {
            LogEngine::writeErrorLog("Ao enviar opinião de estudante: {$e->getMessage()}. Subscription ID: $subscriptionId");
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
    }
}