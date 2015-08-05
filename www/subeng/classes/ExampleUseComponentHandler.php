<?php
require_once APP_ROOT . '/classes/sys/CBaseHandler.php';

require_once APP_ROOT . '/units/select_tree/php/SelectTree.php';
require_once APP_ROOT . '/units/location_select/php/LocationSelect.php';
class ExampleUseComponentHandler extends CBaseHandler{
	/** @var selectTree objects*/
	public $small_categories_list;
	public $big_categories_list;
	
	/** @var locationSelect object*/
	public $location_inputs;
	
	public function __construct() {
		$this->css[] = 'simple';
		$this->js[] = 'simple';
		$this->right_inner = 'select_tree_example.tpl.php';
		parent::__construct();
		$this->small_categories_list = new SelectTree($this, 'pcs');
		$this->big_categories_list = new SelectTree($this, 'product_categories', 'get_pct-2');
		$this->location_inputs = new LocationSelect($this);
	}
}
