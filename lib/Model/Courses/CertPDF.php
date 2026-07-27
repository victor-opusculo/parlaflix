<?php
namespace VictorOpusculo\Parlaflix\Lib\Model\Courses;

use chillerlan\QRCode\QRCode;
use DateTime;
use DateTimeZone;
use Normalizer;
use tFPDF;
use VictorOpusculo\Parlaflix\Lib\Helpers\CertImagick as HelpersCertImagick;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\System;
use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;


class CertPDF extends tFPDF
{
    private DateTime $subscriptionDateTime;
    private DateTime $endDateTime;
    private string $bodyText;
    private string $studentName;
    private int $studentScoredPoints;
    private int $maxScorePossible;
    private int $minScoreRequired;
    private float $hours;
    private array $authInfos;
    private Course $course;

    public function setData(DateTime $subscriptionDateTime,
                            DateTime $endDateTime,
                            string $bodyText,
                            string $studentName,
                            int $studentScoredPoints,
                            int $maxScorePossible,
                            int $minScoreRequired,
                            float $hours,
                            array $authInfos,
                            Course $course) : void
    {

        $this->subscriptionDateTime = $subscriptionDateTime->setTimezone(new DateTimeZone("America/Sao_Paulo"));
        $this->endDateTime = $endDateTime->setTimezone(new DateTimeZone("America/Sao_Paulo"));
        $this->bodyText = $bodyText;
        $this->studentName = $studentName;
        $this->studentScoredPoints = $studentScoredPoints;
        $this->maxScorePossible = $maxScorePossible;
        $this->minScoreRequired = $minScoreRequired;
        $this->hours = $hours;
        $this->authInfos = $authInfos;
        $this->course = $course;



        $this->AddFont("freesans", "", "FreeSans-LrmZ.ttf", true); 
		$this->AddFont("freesans", "B", "FreeSansBold-Xgdd.ttf", true);
		$this->AddFont("freesans", "I", "FreeSansOblique-ol30.ttf", true);
		$this->AddFont("abrilfatface", "", "AbrilFatface-Regular.ttf", true);
    }

    public function drawFrontPage() : void
    {
        $this->AddPage();

        $this->Image(CERT_BG, 0, 0, 297, 210); // Face page

        $this->setY(85);
        $this->SetX(10);
        $this->SetFont('abrilfatface', '', 25);
        $this->SetTextColor(0xB, 0x7B, 0x77);
        $this->MultiCell(277, 13, $this->studentName, 0, "C"); //Student name


        $this->setY(105);
        $this->SetFont('freesans', '', 14);
        $this->SetX(20);
        $this->SetTextColor(0x19, 0x1b, 0x5c);
        $this->MultiCell(257, 6, $this->bodyText . " Pontuação obtida de {$this->studentScoredPoints} de {$this->minScoreRequired} mínimo (máximo possível: {$this->maxScorePossible}). " .
        "Iniciado em {$this->subscriptionDateTime->format('d/m/Y')}, " .
        "terminado em {$this->endDateTime->format('d/m/Y')}, " .
        "com carga horária de {$this->hours}h.", 0, "C"); // Body text
        $this->Ln(5);
    }

    public function drawBackPage() : void
    {
        $this->AddPage();
        $this->Image(CERT_BG2, 0, 0, 297, 210); // Face page

        $TABLE_CELL_WIDTH = 180;

        $this->SetXY(90, 66);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('freesans', '', 12);
        $this->MultiCell($TABLE_CELL_WIDTH, 5, $this->studentName);
        
        $this->SetXY(90, 66 + 15);
        $this->MultiCell($TABLE_CELL_WIDTH, 5, $this->course->name->unwrapOr(""));

        $this->SetXY(90, 66 + 15 + 15);
        $this->MultiCell($TABLE_CELL_WIDTH, 5, Data::formatCourseHourNumber($this->hours) . 'h');

        $this->SetXY(90, 66 + 15 + 15 + 16);
        $this->MultiCell($TABLE_CELL_WIDTH, 5, $this->subscriptionDateTime->format('d/m/Y'));

        $this->SetXY(90, 66 + 15 + 15 + 16 + 15);
        $this->MultiCell($TABLE_CELL_WIDTH, 5, $this->endDateTime->format('d/m/Y'));

        $this->drawAuthenticationInfo(90, 66 + 15 + 15 + 16 + 15 + 15, 15, $TABLE_CELL_WIDTH);

        // $this->SetXY(90, );
        // $this->MultiCell($TABLE_CELL_WIDTH, 5, $this->endDateTime->format('d/m/Y'));
    }

    private function formatEndDate(DateTime $dateTime)
	{
		$monthNumber = (int)$dateTime->format("m");
		$monthName = "";
		switch ($monthNumber)
		{
			case 1: $monthName = "janeiro"; break;
			case 2: $monthName = "fevereiro"; break;
			case 3: $monthName = "março"; break;
			case 4: $monthName = "abril"; break;
			case 5: $monthName = "maio"; break;
			case 6: $monthName = "junho"; break;
			case 7: $monthName = "julho"; break;
			case 8: $monthName = "agosto"; break;
			case 9: $monthName = "setembro"; break;
			case 10: $monthName = "outubro"; break;
			case 11: $monthName = "novembro"; break;
			case 12: $monthName = "dezembro"; break;
		}
		
		$dayNumber = (int)$dateTime->format("j") === 1 ? ("1º") : $dateTime->format("j");
		
		return $dayNumber . " de " . ($monthName) . " de " . $dateTime->format("Y");
	}

    public function getAuthLink()
    {
        $code = $this->authInfos["code"];

        $link = System::getHttpProtocolName() . "://" . $_SERVER["HTTP_HOST"] . 
            URLGenerator::generatePageUrl("/certificate/auth", [ 'code' => $code, 'date' => $this->authInfos["issueDateTime"]->format("Y-m-d"), 'time' => $this->authInfos["issueDateTime"]->format("H:i:s") ]);
    
        return $link;
    }

    public function Footer()
    {
        if ($this->PageNo() == 2)
            $this->drawAuthenticationInfoQR($this->getAuthLink());
    }

    private function drawAuthenticationInfo(int $x, int $y, int $step, int $cellWidth)
	{
		$this->SetXY($x, $y);
        //$this->SetTextColor(0, 0, 0);
		//$this->SetFont("freesans", "I", 8);
		
		$code = $this->authInfos["code"];
		$issueDateTime = $this->authInfos["issueDateTime"]->format("d/m/Y H:i:s");
				
		$authText = "Código $code - Emissão inicial em $issueDateTime (horário de Brasília).";
		$this->MultiCell($cellWidth, 5, $authText, 0, "L");

		$this->SetXY($x, $y + $step);
		$this->MultiCell($cellWidth, 5, AUTH_ADDRESS, 0, "L");

        $link = $this->getAuthLink();
        $this->Link($x, $y + $step, 73, 20, $link);

        return $link;
	}

    private function drawAuthenticationInfoQR(string $link)
	{
        $qr = new QRCode(HelpersCertImagick::options())->render($link);
        $tempFile = tmpfile();
        fwrite($tempFile, $qr);
        fseek($tempFile, 0);

        $tmpPath = stream_get_meta_data($tempFile)['uri'];

        $this->Image($tmpPath, 223, 170, 35, 35, 'PNG');

        fclose($tempFile);

	}
}