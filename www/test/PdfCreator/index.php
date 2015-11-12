<?php
require_once dirname(__FILE__) . '/dss/DssParser.php';
require_once dirname(__FILE__) . '/dss/PrintManager.php';
require_once dirname(__FILE__) . '/flashemu/Sprite.php';
require_once dirname(__FILE__) . '/../DbProvider.php';

function main() {
	//Это просто для теста, потом перенесено в защищенную зону будет
	$id = isset($_GET['id']) ? $_GET['id'] : 0;
	if ($id) {
		$filepath = getFile($id);
		if ($filepath) {
			$sprite = new Sprite();
			$parser = new DssParser();
			$parser->parseData($filepath);
			//var o = PrintManager.preparePrint(dssLoader.parsedData, formView.layer2.viewArea, true/*, pj*/);
			$o = PrintManager::preparePrint($parser, $sprite, true);
			if ($o == false) {
				throw new Exception('Unable prepare print data');
			}
		}
	}
	$data = json_encode($sprite->toArray());//TODO toArray запилить
	echo $data;
	die;
}
//TODO потом перенести куда-нибудь в класс
function getFile($id) {
	$filepath = false;
	DbProvider::setConnection();
	$info = DbProvider::getFileInfo($id);
	if ($info) {
		$filepath = $info->path;
		$filepath = explode('userfiles', $filepath);
		$filepath = 'userfiles' . $filepath[1];
	}
	return $filepath;
}

main();

