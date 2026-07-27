<?php
declare(strict_types=1);


namespace VictorOpusculo\Parlaflix\Lib\Helpers;


use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRImagick;
use chillerlan\QRCode\Output\{QRGdImagePNG};

class CertImagick
{
    public static function options() 
    {
        $options = new QROptions;

		$options->version             = 7;
		$options->outputInterface     = QRGdImagePNG::class;
		$options->scale               = 20;
		$options->outputBase64        = false;
		$options->bgColor             = [200, 150, 200];
		$options->imageTransparent    = true;
		#$options->transparencyColor   = [233, 233, 233];
		$options->drawCircularModules = true;
		$options->drawLightModules    = true;
		// $options->circleRadius        = 0.4;
		$options->keepAsSquare        = [
			QRMatrix::M_FINDER_DARK,
			QRMatrix::M_FINDER_DOT,
			QRMatrix::M_ALIGNMENT_DARK,
		];
		$options->moduleValues        = [];

        return $options;
    }

}