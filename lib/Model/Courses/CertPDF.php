<?php
namespace VictorOpusculo\Parlaflix\Lib\Model\Courses;

use DateTime;
use DateTimeZone;
use tFPDF;

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

    public function setData(DateTime $subscriptionDateTime,
                            DateTime $endDateTime,
                            string $bodyText,
                            string $studentName,
                            int $studentScoredPoints,
                            int $maxScorePossible,
                            int $minScoreRequired,
                            int $hours,
                            array $authInfos) : void
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

        $this->AddFont("freesans", "", "FreeSans-LrmZ.ttf", true); 
		$this->AddFont("freesans", "B", "FreeSansBold-Xgdd.ttf", true);
		$this->AddFont("freesans", "I", "FreeSansOblique-ol30.ttf", true);
    }

    public function drawFrontPage() : void
    {
        $this->AddPage();

        $this->Image(CERT_BG, 0, 0, 297, 210, "JPG"); // Face page

        $this->setY(75);
        $this->SetX(42.5);
        $this->SetFont('freesans', 'B', 24);
        $this->MultiCell(212, 13, $this->studentName, 0, "C"); //Student name

        $this->SetFont('freesans', '', 13);
        $this->SetX(42.5);
        $this->MultiCell(212, 6, $this->bodyText, 0, "C"); // Body text
        $this->Ln(5);

        $this->SetFont('freesans', '', 13);
        $this->SetX(42.5);
        $this->MultiCell(212, 6, "Pontuação obtida de {$this->studentScoredPoints} de {$this->minScoreRequired} mínimo (máximo possível: {$this->maxScorePossible}). " .
        "Curso iniciado em {$this->subscriptionDateTime->format('d/m/Y')}, " .
        "terminado em {$this->endDateTime->format('d/m/Y')}, " .
        "cumprindo carga horária de {$this->hours}h."
        , 0, 'C');

        $this->drawAuthenticationInfo();
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
		$this->SetX(150);
		$this->SetY($this->GetPageHeight() - 55);
		$this->SetFont("freesans", "I", 11);
		
		$code = $this->authInfos["code"];
		$issueDateTime = $this->authInfos["issueDateTime"]->format("d/m/Y H:i:s");
				
		$authText = "Verifique a autenticidade deste certificado em: " . AUTH_ADDRESS . " e informe os seguintes dados: Código $code - Emissão inicial em $issueDateTime (horário de Brasília).";
		$this->MultiCell(100, 5, $authText, 0, "L");
	}
}