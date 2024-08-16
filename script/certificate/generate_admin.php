<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../lib/Helpers/LogEngine.php';

use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\Parlaflix\Lib\Helpers\System;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\Parlaflix\Lib\Helpers\UserTypes;
use VictorOpusculo\Parlaflix\Lib\Model\Administrators\Administrator;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\CertPDF;
use VictorOpusculo\Parlaflix\Lib\Model\Database\Connection;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\GeneratedCertificate;
use VictorOpusculo\Parlaflix\Lib\Model\Media\Media;
use VictorOpusculo\Parlaflix\Lib\Model\Settings\CertificateBackground2MediaId;
use VictorOpusculo\Parlaflix\Lib\Model\Settings\CertificateBackgroundMediaId;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Student;
use VictorOpusculo\Parlaflix\Lib\Model\Students\Subscription;

$certBgMediaId = null;
$certBgMedia = null;
$certBgMedia2Id = null;
$certBgMedia2 = null;
$conn = Connection::get();
try
{
    $certBgMediaId = (new CertificateBackgroundMediaId)->getSingle($conn)->value->unwrap();
    $certBgMedia = (new Media([ 'id' => $certBgMediaId ]))->getSingle($conn);
    $certBgMedia2Id = (new CertificateBackground2MediaId)->getSingle($conn)->value->unwrap();
    $certBgMedia2 = (new Media([ 'id' => $certBgMedia2Id ]))->getSingle($conn);
}
catch (Exception $e)
{
    die("Erro: Imagem modelo de certificado não cadastrada!");
}

define('AUTH_ADDRESS',  System::getHttpProtocolName() . "://" . $_SERVER["HTTP_HOST"] . URLGenerator::generatePageUrl("/certificate/auth"));
define('CERT_BG', $certBgMedia->fullFileName());
define('CERT_BG2', $certBgMedia2->fullFileName());
define('_SYSTEM_TTFONTS', __DIR__ . '/../../assets/fonts/');

$subsId = isset($_GET['subscription_id']) && is_numeric($_GET['subscription_id']) ? (int)$_GET['subscription_id'] : null;

if (!Connection::isId($subsId))
    die("ID inválido!");



session_name('parlaflix_admin_user');
session_start();

if (!isset($_SESSION) || $_SESSION['user_type'] !== UserTypes::administrator)
    die("Administrador não logado!");

if (!(new Administrator([ 'id' => $_SESSION['user_id'] ]))->exists($conn))
    die("Administrador não localizado!");

$subscription = (new Subscription([ 'id' => $subsId, 'student_id' => $_SESSION['user_id'] ]))->getSingleWithProgressData($conn);
$subscription->fetchCourse($conn);

$scoredPoints = $subscription->getOtherProperties()->studentPoints;
$maxScorePossible = $subscription->getOtherProperties()->maxPoints;

$studentGetter = new Student([ 'id' => $subscription->student_id->unwrapOrElse(fn() => throw new Exception("Aluno não localizado!")) ]);
$studentGetter->setCryptKey(Connection::getCryptoKey());
$student = $studentGetter->getSingle($conn);

if ($scoredPoints < $subscription->course->min_points_required->unwrap())
    die("Aluno não foi aprovado neste curso!");

$issueDateTime = $endDateTime = new DateTime('now', new DateTimeZone('UTC'));
$genCertificate = (new GeneratedCertificate([ 'course_id' => $subscription->course->id->unwrap(), 'student_id' => $student->id->unwrap(), 'datetime' => $issueDateTime->format('Y-m-d H:i:s') ]));
$certId = 0;
if (!$genCertificate->existsByCourseAndStudent($conn))
{
    $result = $genCertificate->save($conn);
    if ($result['newId'])
        $certId = $result['newId'];
    else
    {
        LogEngine::writeErrorLog("Erro ao registrar certificado. Inscrição ID: {$subscription->id->unwrapOr(-1)}");
        die("Erro ao registrar o certificado!");
    }
}
else
{
    $cert = $genCertificate->getByCourseAndStudent($conn);
    $certId = $cert->id->unwrap();
    $issueDateTime = $endDateTime = new DateTime($cert->datetime->unwrap(), new DateTimeZone("UTC"));
}

$conn->close();

$pdf = new CertPDF('L', 'mm', 'A4');
$pdf->setData(  subscriptionDateTime: new DateTime($subscription->datetime->unwrap(), new DateTimeZone("UTC")),
                endDateTime: $endDateTime,
                bodyText: $subscription->course->certificate_text->unwrap(),
                studentName: $student->full_name->unwrap(),
                studentScoredPoints: $scoredPoints,
                maxScorePossible: $maxScorePossible,
                minScoreRequired: $subscription->course->min_points_required->unwrap(),
                hours: $subscription->course->hours->unwrap(),
                authInfos: [ 'code' => $certId, 'issueDateTime' => $issueDateTime->setTimezone(new DateTimeZone("America/Sao_Paulo")) ],
                course: $subscription->course
             );

$pdf->drawFrontPage();
$pdf->drawBackPage();

header('Content-Type: application/pdf');
header('Content-Disposition: filename="'. $subscription->course->name->unwrapOr('Curso') .'.pdf"');
echo $pdf->Output('S');

LogEngine::writeLog("Certificado gerado pela administração. ID: $certId. Curso ID: {$subscription->course->id->unwrapOr(-1)}");