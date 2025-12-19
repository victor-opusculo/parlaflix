<?php
namespace VictorOpusculo\Parlaflix\Api\Student\Subscribe;

use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Helpers\UserTypes;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Student;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Subscription;
use VictorOpusculo\PComp\RouteHandler;

require_once __DIR__ . '/../../../lib/Middlewares/StudentLoginCheck.php';

final class CourseId extends RouteHandler
{

    public function __construct()
    {
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\studentLoginCheck';
    }

    protected $courseId;

    protected function POST(): void
    {
        if ($_SESSION['user_type'] !== UserTypes::student)
        {
            $this->json([ 'error' => 'Você precisa estar logado como estudante!' ], 500);
            exit;
        }

        $conn = Connection::get();
        $course = (new Course([ 'id' => $this->courseId ]))->getSingle($conn);
        $courseExists = $course->exists($conn);
        $isOnlyForMembers = (bool)$course->members_only->unwrapOr(0);
        if (!$courseExists)
        {
            $this->json([ 'error' => 'Curso não existente' ], 404);
            exit;
        }

        if ($course->is_external->unwrapOr(0))
        {
            $this->json([ 'error' => 'Este curso é feito em outra plataforma' ], 500);
            exit;
        }

        $studentGetter = (new Student([ 'id' => $_SESSION['user_id'] ]));
        $studentGetter->setCryptKey(Connection::getCryptoKey());
        $student = $studentGetter->getSingle($conn);
        $isAbelMember = (bool)$student->is_abel_member->unwrapOr(0);

        if ($isOnlyForMembers && !$isAbelMember)
        {
            $this->json([ 'error' => 'Este curso é exclusivo para associados!' ], 500);
            exit;
        }

        $newSubs = (new Subscription([ 'course_id' => $this->courseId, 'student_id' => $_SESSION['user_id'], 'datetime' => gmdate('Y-m-d H:i:s') ]));

        if ($newSubs->isStudentSubscribed($conn))
        {
            $this->json([ 'error' => 'Você já está inscrito neste curso!' ], 500);
            exit;
        }

        $result = $newSubs->save($conn);
        if ($result['newId'])
        {
            LogEngine::writeLog("Inscrição em curso feita! Estudante ID: {$_SESSION['user_id']}. Curso ID: {$this->courseId}. Inscrição ID: {$result['newId']}");
            $this->json([ 'success' => 'Você se inscreveu no curso!' ]);
        }
        else
        {
            LogEngine::writeErrorLog("Erro ao inscrever estudante em curso. Estudante ID: {$_SESSION['user_id']}. Curso ID: {$this->courseId}");
            $this->json([ 'error' => 'Não foi possível se increver no curso. Contate o suporte do Parlaflix.' ], 500);
        }
    }
}