<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
	<title>Основы программирования - веб-консоль</title>
	<link rel="stylesheet" type="text/css" href="<?=WEB_ROOT ?>/css/style.css?v=<?=STATIC_VERSION?>" />
	<link rel="stylesheet" type="text/css" href="<?=WEB_ROOT ?>/css/popupwin.css?v=<?=STATIC_VERSION?>" /><?
	if (isset($handler->css) && is_array($handler->css) && count($handler->css)) {
		foreach ($handler->css as $css){
		?><link rel="stylesheet" type="text/css" href="<?=WEB_ROOT ?>/css/<?=$css?>.css?v=<?=STATIC_VERSION?>" /><?
		}
	}
	?><script type="text/javascript" src="<?=WEB_ROOT ?>/js/jquery-1.11.0.min.js"></script>
	<script type="text/javascript" src="<?=WEB_ROOT ?>/js/jquery.cookie.js"></script>
	<script type="text/javascript" src="<?=WEB_ROOT ?>/js/popupwin.js"></script>
	<script type="text/javascript" src="<?=WEB_ROOT ?>/js/local.js.php"></script>
	<script type="text/javascript" src="<?=WEB_ROOT ?>/js/lib.js?v=<?=STATIC_VERSION?>"></script>
	<script type="text/javascript" src="<?=WEB_ROOT ?>/js/app.js?v=<?=STATIC_VERSION?>"></script>
	<script type="text/javascript" >
		var WEB_ROOT = '<?=WEB_ROOT?>';
	</script>
	<?
	if (isset($handler->js) && is_array($handler->js) && count($handler->js)) {
		foreach ($handler->js as $js){
		?><script type="text/javascript" charset="UTF-8" src="<?=WEB_ROOT ?>/js/<?=$js?>?v=<?=STATIC_VERSION?>"></script>
	<?
		}
	}
	?>
	<? if (o($handler, 'file_list')):?>
		<? foreach($handler->file_list as $task): ?>
		<script type="text/javascript" charset="UTF-8" src="<?=WEB_ROOT ?>/js/task.js.php?id=<?=$task['id']?>"></script>
		<? endforeach?>
	<? endif?>
				
<?php if (sess('messages')):?>
	<script type="text/javascript">
		var infoMessages = <?=json_encode(sess('messages'))?>;
		<? unset($_SESSION['messages']);?>
	</script>
<? else:?>
	<script type="text/javascript">
		var infoMessages = {};
	</script>
<? endif ?>
</head>
<body><img src="/img/std/back_text.png" class="pload" />
	<div class="main center relative">
		<div class="toolbar">
			<? include APP_ROOT . '/tpl/std/toolbar.php'?>
		</div>
			<? include APP_ROOT . '/tpl/std/form_auth.tpl.php' ?>
		
		<div id="tooltip" class="right bg-dark-blue"></div>
		<div class="center res_page_content">
			<?
				if (isset($handler->right_inner)) {
					include APP_ROOT . '/tpl/' . $handler->right_inner;
				}
			 ?>
		</div>
		<div class="clearfix"></div>
	</div>
	<div id="ldrbig" class="hide">
		<img src="<?=WEB_ROOT ?>/img/ploader.gif" />
	</div>
	<div class="popupbg hide"id="popupbg"></div>
	<div class="popupbg z6 hide"id="loaderbg"></div>
	<div class="adminpopup hide" id="popup">
		<div class="popup-title-area">
			<div id="popuptitle">...</div>
			<div class="popup-close" onclick="appWindowClose()">
				<img src="<?=WEB_ROOT ?>/img/std/close.png" />
			</div>
		</div>
		<div class="both"></div>
		<div class="popup-content" id="appWindowPopup"></div>
	</div>
	<div id="addScriptFormWrapper" class="hide">
		<form id="addScriptForm" class="upopup" enctype="multipart/form-data" method="POST">
			<fieldset>
				<legend>Информация</legend>
				<p>
					Загрузите файл с исходным кодом программы на языке яваскрипт.
				</p>
				<p>
					Файл должен содержать одну главную функцию, имя которой должно совпадать с именем файла.
				</p>
				<p>
					Например, файл называется task1.js, имя главной функции должно быть task1.
				</p>
				<p>
					Все остальные функции должны быть определены внутри главной.
				</p>
			</fieldset>
			<div class="uploadFormInputs">
				<p class="text-right">
					<label for="scriptDisplayName">Выберите файл скрипта</label>
					<span class="red">*</span><input type="file" id="scriptFile" name="scriptFile"/>
				</p>
				<p >
					<label for="scriptDisplayName">Имя программы в меню слева</label>
					<span class="red"></span><input type="text" value="" id="scriptDisplayName" name="scriptDisplayName"/>
				</p>
				<p class="text-right">
					<input type="submit" id="scriptFileSubmit" name="scriptFileSubmit"/>
					<input type="hidden" id="edit_id" name="edit_id"/>
				</p>
			</div>
		</form>
	</div>
	
	<div id="saveScriptFormWrapper" class="hide">
		<form id="saveScriptForm" class="upopup" method="POST">
			<fieldset>
				<legend>Информация</legend>
				<p>
					Сохраняемый код должен содержать одну главную функцию.
				</p>
				<p>
					Например:<pre>
function myFirstProgram() {
	//Тут все остальное, включая вспомогательные функции
}
					</pre>
				</p>
			</fieldset>
			<div class="uploadFormInputs">
				<p>
					<label for="scriptDisplayNameQs">Имя программы</label>
					<span class="red"></span><input type="text" value="" id="scriptDisplayNameQs" name="scriptDisplayNameQs"/>
					<input type="hidden" value="" id="scriptFileNameQs" name="scriptFileNameQs"/>
				</p>
				<p class="text-right">
					<input type="button" id="scriptFileQsButton" name="scriptFileQsButton" value="<?=$lang['Save']?>"/>
				</p>
			</div>
		</form>
	</div>
	<? include APP_ROOT . '/tpl/std/form_reg.tpl.php' ?>
	<? include APP_ROOT . '/tpl/std/show_your_task.tpl.php'?>
</body>
</html> 
