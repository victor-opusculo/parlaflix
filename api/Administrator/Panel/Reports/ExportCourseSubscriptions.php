<?php
namespace VictorOpusculo\Parlaflix\Api\Administrator\Panel\Reports;

use DateTimeZone;
use Exception;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Subscription;
use VictorOpusculo\PComp\RouteHandler;
use VOpus\PhpOrm\Exceptions\DatabaseEntityNotFound;

require_once __DIR__ . '/../../../../lib/Middlewares/AdminLoginCheck.php';

final class ExportCourseSubscriptions extends RouteHandler
{
    public function __construct()
    {
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\adminLoginCheck';
    }

    protected function GET(): void
    {
        $courseId = $_GET['course_id'] ?? 0;
        $conn = Connection::get();
        try
        {
            $subscriptions = (new Subscription([ 'course_id' => $courseId ]))
            ->setCryptKey(Connection::getCryptoKey())
            ->getAllFromCourseForReport($conn, $_GET['q'] ?? '', $_GET['order_by'] ?? '');

            $course = (new Course([ 'id' => $courseId ]))->getSingle($conn);

            if (count($subscriptions) < 1)
                die("Não há dados disponíveis segundo o critério atual de pesquisa.");

            header('Content-Encoding: UTF-8');
            header("Content-type: text/csv; charset=UTF-8");
            header("Content-Disposition: attachment; filename=inscricoes-parlaflix--{$course->name->unwrapOr('')}.csv");

            $procData = Data::transformDataRows($subscriptions,
            [
                'Inscrição ID' => fn($s) => $s->id->unwrapOr(0),
                'Data de inscrição' => fn($s) => date_create($s->datetime->unwrapOr('now'), new DateTimeZone('UTC'))->setTimezone(new DateTimeZone($_SESSION['user_timezone']))->format('d/m/Y H:i:s'),
                'Curso' => fn($s) => $course->name->unwrapOr(''),
                'Estudante' => fn($s) => $s->getOtherProperties()->studentName ?? '',
                'E-mail' => fn($s) => $s->getOtherProperties()->studentEmail ?? '',
                'Telefone' => fn($s) => $s->getOtherProperties()->studentTelephone ?? '',
                'Aulas completadas' => fn($s) => $s->getOtherProperties()->doneLessonCount ?? 0,
                'Total de aulas' => fn($s) => $s->getOtherProperties()->lessonCount ?? 0
            ]);

            $output = fopen("php://output", "w");
            $header = array_keys($procData[0]);

            fwrite($output, "\xEF\xBB\xBF" . PHP_EOL);
            fputcsv($output, $header, ";", '"', "\\");

            foreach($procData as $row)
                fputcsv($output, $row, ";", '"', "\\");

            fclose($output);
        }
        catch (Exception $e)
        {
            die($e->getMessage());
        }
    }
}