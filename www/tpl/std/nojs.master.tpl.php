<!DOCTYPE html>
<html class="h100">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
	<title><?=$app->title()?></title>
	<link rel="stylesheet" type="text/css" href="<?=WEB_ROOT ?>/css/style.css?v=<?=STATIC_VERSION?>" />
	<link rel="stylesheet" type="text/css" href="<?=WEB_ROOT ?>/css/popupwin.css?v=<?=STATIC_VERSION?>" /><?
	if (isset($handler->css) && is_array($handler->css) && count($handler->css)) {
		foreach ($handler->css as $css){
		?><link rel="stylesheet" type="text/css" href="<?=WEB_ROOT ?>/css/<?=$css?>.css?v=<?=STATIC_VERSION?>" />
<?
		}
	}
	?>
</head>
<body>
	<div class="main center relative h100">
		<div class="toolbar">
			<? include APP_ROOT . '/tpl/std/toolbar.php'?>
		</div>
		<div class="simple_page_content">
			<?=messages_ext($handler)?>
			<?
				if (isset($handler->right_inner)) {
					include APP_ROOT . '/tpl/' . $handler->right_inner;
				}
			?>
		</div>
		<footer>
			&copy; Lamzin Andrey, 2014.
		</footer>
	</div>
</body>
</html> 
