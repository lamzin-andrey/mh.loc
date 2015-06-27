<?php
require_once APP_ROOT . '/classes/sys/CBaseHandler.php';

require_once APP_ROOT . '/units/select_tree/php/SelectTree.php';
class ExampleUseComponentHandler extends CBaseHandler{
	public $small_categories_list;
	public $big_categories_list;
	public function __construct() {
		$this->css[] = 'simple';
		$this->js[] = 'simple';
		$this->right_inner = 'select_tree_example.tpl.php';
		parent::__construct();
		$this->small_categories_list = new SelectTree($this, 'product_categories');
		$this->big_categories_list = new SelectTree($this, 'product_categories_test_2', 'get_pct-2');
	}
}
