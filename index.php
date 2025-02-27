<?php

use VictorOpusculo\Parlaflix\Lib\Helpers\URLGenerator;
use VictorOpusculo\PComp\{HeadManager, StyleManager, ScriptManager, AppInitializer};
use function VictorOpusculo\PComp\Prelude\render;

require_once "vendor/autoload.php";

$app = new AppInitializer(require_once __DIR__ . '/app/router.php');

URLGenerator::loadConfigs();

?><!DOCTYPE HTML>
<html>
	<head>
		<!-- Desenvolvido por Victor Opusculo -->
		<script>
			const useFriendlyUrls = <?= URLGenerator::$useFriendlyUrls ? 'true' : 'false' ?>;
			const baseUrl = '<?= URLGenerator::$baseUrl ?>';
			const Parlaflix = {};

			if (window.localStorage.darkMode === '1')
				document.documentElement.classList.add("dark");
		</script>
		<script src="<?= URLGenerator::generateFileUrl('assets/script/AlertManager.js') ?>"></script>
		<script src="<?= URLGenerator::generateFileUrl('assets/script/URLGenerator.js') ?>"></script>
		<script src="<?= URLGenerator::generateFileUrl('client-components/dist/index.js') ?>" type="module"></script>
		<meta charset="utf8"/>
		<meta content="width=device-width, initial-scale=1" name="viewport" />
		<meta name="description" content="Plataforma EAD da ABEL">
		<meta name="keywords" content="">
  		<meta name="author" content="Victor Opusculo Oliveira Ventura de Almeida">
		<link rel="stylesheet" href="<?= URLGenerator::generateFileUrl('assets/twoutput.css') ?>" />
		<link rel="shortcut icon" type="image/x-icon" href="<?= URLGenerator::generateFileUrl("assets/favicon.ico") ?>" />
		<link rel="preload" href="<?= URLGenerator::generateFileUrl('assets/pics/star_empty.png') ?>" as="image" />
		<link rel="preload" href="<?= URLGenerator::generateFileUrl('assets/pics/star_filled.png') ?>" as="image" />
		<?= HeadManager::getHeadText() ?>
		<?= StyleManager::getStylesText() ?>
	</head>
	<body>
		<?php render($app->mainFrameComponents); ?>
	</body>
	<?= ScriptManager::getScriptText() ?>
	<script
		src="https://cdn.jsdelivr.net/npm/shareon@2/dist/shareon.iife.js"
		defer
		init
	></script>
</html>