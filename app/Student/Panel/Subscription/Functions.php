<?php
namespace VictorOpusculo\Parlaflix\App\Student\Panel\Subscription;

require __DIR__ . "/../../../../lib/Middlewares/StudentLoginCheck.php";

use Exception;
use VictorOpusculo\Parlaflix\Lib\Helpers\LessonTests;
use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Subscription;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestCompleted;
use VictorOpusculo\Parlaflix\Lib\Model\Tests\TestSkel;
use VictorOpusculo\PComp\Rpc\BaseFunctionsClass;
use VictorOpusculo\PComp\Rpc\HttpGetMethod;
use VictorOpusculo\PComp\Rpc\ReturnsContentType;

final class Functions extends BaseFunctionsClass
{
    protected array $middlewares = ['\VictorOpusculo\Parlaflix\Lib\Middlewares\studentLoginCheck'];

    public function receiveTestAnswers(array $data) : array
    {
        $conn = Connection::get();
        try
        {
            $lessonId = $data['lesson_id'] ?? 0;
            $studentId = $_SESSION['user_id'] ?? 0;
            $subscriptionId = $data['subscription_id'] ?? 0;

            $answers = $data['answers'] ?? [];

            $skelDr = new TestSkel([ 'lesson_id' => $lessonId ])->getFromLessonId($conn);
            $skelRaw = $skelDr->buildStructure(null);

            [ $skel, $correctQuestions ] = LessonTests::setAndCalculateCorrectAnswers($skelRaw, $answers);

            $minRequired = $skelDr->min_percent_for_approval->unwrap();
            $grade = $correctQuestions / count($skel->questions) * 100;

            $subs = new Subscription([ 'id' => $subscriptionId, 'student_id' => $studentId ])->getSingleFromStudent($conn);

            $testComplete = new TestCompleted([ 
                'subscription_id' => $subs->id->unwrap(), 
                'lesson_id' => $lessonId, 
                'test_skel_id' => $skelDr->id->unwrap(),
                'test_data' => $skel->toJson(),
                'is_approved' => $grade >= $minRequired ? 1 : 0
            ]);

            [ $maxed, $attCount ] = $testComplete->studentMaxedAttemps($conn);

            if ($maxed)
                throw new Exception("Você atingiu o número máximo de tentativas!");

            if ($testComplete->getStudentApprovedTest($conn) !== false)
                throw new Exception("Você já foi aprovado neste questionário!");

            $result = $testComplete->save($conn);
            $userResult = $grade >= $minRequired ? "APROVADO" : "REPROVADO";
            $gradeFormated = number_format($grade, 1, ",", ".");
            if ($result['newId'])
            {
                LogEngine::writeLog("Estudante enviou resposta de questionário. Novo ID: {$result['newId']} | Inscrição ID: {$subs->id->unwrapOr("***")}");
                return [ 'success' => "Questionário enviado! Você foi $userResult com {$gradeFormated}% de acerto." ];
            }
            else
            {
                LogEngine::writeErrorLog("Ao enviar resposta de questionário. Novo ID faltando | Inscrição ID: {$subs->id->unwrapOr("***")}");
                return [ 'error' => "Erro ao salvar o questionário!" ];
            }
        }
        catch (Exception $e)
        {
            LogEngine::writeErrorLog("Ao enviar resposta de questionário. Estudante ID: {$_SESSION['user_id']}");
            return [ 'error' => $e->getMessage() ];
        }
    }

    #[HttpGetMethod]
    public function removeSubscription(array $query) : array
    {
        $conn = Connection::get();
        try
        {
            [ 'id' => $subscriptionId ] = $query;
            $studentId = $_SESSION['user_id'] ?? 0;

            $subs = new Subscription([ 'id' => $subscriptionId, 'student_id' => $studentId ])->getSingleFromStudent($conn);

            $result = $subs->delete($conn);
            if ($result['affectedRows'] > 0)
            {
                LogEngine::writeLog("Inscrição removida pelo próprio estudante. Inscrição ID: $subscriptionId | Estudante ID: $studentId");
                return [ 'success' => "Inscrição removida com sucesso!" ];
            }
            else
            {
                LogEngine::writeErrorLog("Ao remover inscrição pelo próprio estudante. Estudante ID: {$_SESSION['user_id']}");
                return [ 'error' => "Erro ao excluir inscrição" ];
            }
        }
        catch (Exception $e)
        {
            LogEngine::writeErrorLog("Ao remover inscrição pelo próprio estudante. Estudante ID: {$_SESSION['user_id']}");
            return [ 'error' => $e->getMessage() ];
        }
    }
}