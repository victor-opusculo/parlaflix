<?php
namespace VictorOpusculo\Parlaflix\Lib\Model\Courses;

use DateTime;
use DateTimeZone;
use Normalizer;
use tFPDF;
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
    private int $hours;
    private array $authInfos;
    private Course $course;

    public function setData(DateTime $subscriptionDateTime,
                            DateTime $endDateTime,
                            string $bodyText,
                            string $studentName,
                            int $studentScoredPoints,
                            int $maxScorePossible,
                            int $minScoreRequired,
                            int $hours,
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

        $this->Image(CERT_BG, 0, 0, 297, 210, "JPG"); // Face page

        
        $this->setY(75);
        $this->SetFont('freesans', '', 13);
        $this->SetX(32.5);
        $this->MultiCell(160, 6, $this->bodyText . " Pontuação obtida de {$this->studentScoredPoints} de {$this->minScoreRequired} mínimo (máximo possível: {$this->maxScorePossible}). " .
        "Iniciado em {$this->subscriptionDateTime->format('d/m/Y')}, " .
        "terminado em {$this->endDateTime->format('d/m/Y')}, " .
        "com carga horária de {$this->hours}h.", 0, "C"); // Body text
        $this->Ln(5);


        $this->setY(133.5);
        $this->SetX(10);
        $this->SetFont('abrilfatface', '', 24);
        $this->SetTextColor(0xB, 0x7B, 0x77);
        $this->MultiCell(212, 13, $this->studentName, 0, "C"); //Student name
    }

    public function drawBackPage() : void
    {
        $this->AddPage();
        $this->Image(CERT_BG2, 0, 0, 297, 210, "JPG"); // Face page

        $this->drawAuthenticationInfo();

        $TABLE_CELL_WIDTH = 180;

        $this->SetXY(90, 87);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('abrilfatface', '', 12);
        $this->MultiCell($TABLE_CELL_WIDTH, 5, $this->studentName);
        
        $this->SetXY(90, 87 + 21);
        $this->MultiCell($TABLE_CELL_WIDTH, 5, $this->course->name->unwrapOr(""));

        $this->SetXY(90, 87 + 21 + 22);
        $this->MultiCell($TABLE_CELL_WIDTH, 5, $this->hours . 'h');

        $this->SetXY(90, 87 + 21 + 22 + 22);
        $this->MultiCell($TABLE_CELL_WIDTH, 5, $this->subscriptionDateTime->format('d/m/Y'));

        $this->SetXY(90, 87 + 21 + 22 + 22 + 23);
        $this->MultiCell($TABLE_CELL_WIDTH, 5, $this->endDateTime->format('d/m/Y'));
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

    private function drawAuthenticationInfo()
	{
		$this->SetXY(20, 65);
		$this->SetFont("freesans", "I", 11);
		
		$code = $this->authInfos["code"];
		$issueDateTime = $this->authInfos["issueDateTime"]->format("d/m/Y H:i:s");
				
		$authText = "Verifique a autenticidade deste certificado em: " . AUTH_ADDRESS . " e informe os seguintes dados: Código $code - Emissão inicial em $issueDateTime (horário de Brasília).";
		$this->MultiCell(250, 5, $authText, 0, "L");

        $this->Link(20, 65, 250, 15, 
            System::getHttpProtocolName() . "://" . $_SERVER["HTTP_HOST"] . 
            URLGenerator::generatePageUrl("/certificate/auth", [ 'code' => $code, 'date' => $this->authInfos["issueDateTime"]->format("Y-m-d"), 'time' => $this->authInfos["issueDateTime"]->format("H:i:s") ])
        );
	}
}