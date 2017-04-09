<?php
require_once APP_ROOT . '/classes/sys/CBaseHandler.php';
require_once APP_ROOT . '/units/location_select/php/LocationSelect.php';

class ExampleUseComponentHandler extends CBaseHandler{
	/** @var locationSelect object*/
	public $location_inputs;
	
	public function __construct() {
		$this->css[] = 'simple';
		$this->js[] = 'simple';
		$this->right_inner = 'location_select_example.tpl.php';
		parent::__construct();
		$this->location_inputs = new LocationSelect($this);
	}
}
