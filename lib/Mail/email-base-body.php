<?php

use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;

require_once __DIR__ . '/../../vendor/autoload.php';

foreach ($_GET as $k => $v)
	$$k = $v;
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title></title>
	<style>
		body, table td
		{
			font-size: 20px;
		}
	</style>
</head>

<body bgcolor="#eeeeee">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee">
	<tr>
	<td align="center" bgcolor="#eeeeee">
		<table id="mailFrame" width="600" style="background: #ffffff;
			max-width: 600px;
			min-width: 400px;
			border: 1px solid darkgray; 
			border-radius: 10px;
			padding: 15px 10px 15px 10px;
			box-shadow: 0px 5px 10px 0 rgba(0,0,0,0.2);">
			<tr id="mailHead">
				<td style="display: block;" align="center">
					<img src="<?php echo URLGenerator::getHttpProtocolName() . "://" . $_SERVER["HTTP_HOST"] . URLGenerator::generateFileUrl('assets/pics/parlaflix.png'); ?>" height="80" alt="Parlaflix" />
				</td>
			</tr>
		
			<tr id="mailBody">
				<td style="display: block;" align="center">
					<?php require_once __DIR__ . '/' . $__VIEW; ?>
				</td>
			</tr>

			<tr id="Logos" class="centControl">
				<td style="display: block;" align="center">
					<img src="<?php echo URLGenerator::getHttpProtocolName() . "://" . $_SERVER["HTTP_HOST"] . URLGenerator::generateFileUrl('assets/pics/abel.png'); ?>" alt="ABEL" height="100"/>
				</td>
			</tr>
		</table>
	</td>
	</tr>
	</table>


</body>
</html>