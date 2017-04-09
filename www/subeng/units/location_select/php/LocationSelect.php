<?php
require_once APP_ROOT . '/classes/sys/CBaseHandler.php';
/**@desc Должен позволять удобно добавлять на страницу списки регионов */
class LocationSelect {
	//TODO сделать конфигурацию полей таблиц в коде
	/**@var _handler CBaseHandler child*/
	private $_handler;
	/**@var _field_id name*/
	private $_field_id;
	/**@var _table_regions*/
	private $_table_regions;
	/**@var _table_cities*/
	private $_table_cities;
	/**@var _listen_action - это надо указать в атрибуте data-action*/
	private $_listen_action;
	/**@var _field_region_id name*/
	private $_field_region_id;
	/**@var _field_is_deleted name*/
	private $_field_is_deleted;
	/**@var _field_is_accepted*/
	private $_field_is_accepted;
	/**@var _field_name*/
	private $_field_name;
	/**@var _field_order*/
	private $_field_order;
	/**
	 * @desc 
	 * @param $handler - CBaseHandler or child
	**/
	public function __construct(CBaseHandler $handler/*, $table_regions, $table_cities, $listen_action = 'get_childs', $field_order = 'delta', $field_id = 'id', $field_parent_id = 'parent_id', $field_is_deleted = 'is_deleted',$field_name = 'name', $field_is_accepted = 'is_moderate'*/) {
		
		$this->_handler = $handler;
		/*$this->_table_regions = $table_regions;
		$this->_table_cities = $table_cities;
		$this->_listen_action = $listen_action;
		$this->_field_id = $field_id;
		$this->_field_name = $field_name;
		$this->_field_parent_id = $field_parent_id;
		$this->_field_is_deleted = $field_is_deleted;
		$this->_field_is_accepted = $field_is_accepted;
		$this->_field_order = $field_order;*/
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