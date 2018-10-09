<?php
require_once APP_ROOT . '/classes/sys/CBaseHandler.php';

require_once APP_ROOT . '/units/bread_crumbs/php/BreadCrumbs.php';
class ExampleUseComponentHandler extends CBaseHandler {
	public $breadCrumbs;
	public function __construct() {
		$this->css[] = 'simple';
		$this->js[] = 'simple';
		$this->right_inner = 'bread_crumbs_example.tpl.php';
		parent::__construct();
		$this->breadCrumbs = new BreadCrumbs($this, $a, $aPrefix)
		$aPrefix = ['moskovskaya_oblast', 'kamenogorsk'];//now support no more two elements
		$this->beadCrumbs = new BreadCrumbs($this, ['/'	=>	'Home', 
													'league'	=>	'League of Champions',
													'cap' => 'Capitan Obvius'], $aPrefix);
	}
}
