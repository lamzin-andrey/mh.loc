<?php
require_once APP_ROOT . '/classes/sys/CBaseHandler.php';
/**@desc Должен позволять удобно добавлять на страницу списки регионов */
class FileUploader {
	//TODO сделать конфигурацию полей таблиц в коде
	/**@var _handler CBaseHandler child*/
	private $_handler;
	/**@var _table name*/
	private $_table = 'files';
	/**@var _field_path*/
	private $_field_path;
	/**
	 * @desc 
	 * @param $handler - CBaseHandler or child
	**/
	public function __construct(CBaseHandler $handler, $table, $field) {
		
		$this->_handler = $handler;
		if (!a($handler->components, 'location_select'))  {
			$handler->js[] = WEB_ROOT . '../units/location_select/js/script.js';
			$handler->css[] = WEB_ROOT . '../units/location_select/js/style';
			$handler->components['location_select'] = 1;
		}
		$this->_listen();
	}
	/**
	 * TODO
	 * @desc render html
	*/
	public function block() {
		return file_get_contents(APP_ROOT . '/units/location_select/php/tpl/inputs.tpl.php');
	}
	/**
	 * @desc 
	 * @param $handler - CBaseHandler or child
	*/
	private function _listen() {
		switch (req("action")) {
			case "country":
				$this->loadCountry(); 
			break;
			case "region":
				$this->loadRegion(ireq('countryId')); 
			break;
			case "city":
				$this->loadCity(ireq('regionId')); 
			break;
		}
	}
	/**
	 * @desc 
	 * @param
	*/
	private function loadCity($region) {
		if ($region == 0) {
			$data = query("SELECT id, city_name FROM cities WHERE is_deleted != 1 AND is_moderate = 1 AND region = $region ORDER BY delta");
		} else {
			$data = query("SELECT id, city_name, delta FROM
( (SELECT id, city_name, 0 AS is_city, delta FROM cities WHERE is_deleted != 1 AND is_moderate = 1 AND region = $region )
UNION
(SELECT id, region_name AS city_name, is_city, delta FROM regions WHERE is_deleted != 1 AND is_moderate = 1 AND parent_id = $region ) ) AS data ORDER BY is_city DESC, delta ASC;");
		}
	 	json_ok('list', $data);
	}
	/**
	 * @desc 
	 * @param
	*/
	private function loadRegion($country) {
		$data = query("SELECT id, region_name FROM regions WHERE is_deleted != 1 AND is_moderate = 1 AND country = 3 ORDER BY delta");
	 	json_ok('list', $data);
	}
	/**
	 * @desc 
	 * @param
	*/
	private function loadCountry() {
	 	$data = query("SELECT id, country_name FROM countries WHERE is_deleted != 1 AND is_moderate = 1 ORDER BY delta");
	 	json_ok('list', $data);
	}
	
}