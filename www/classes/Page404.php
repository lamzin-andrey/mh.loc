<?php
require_once APP_ROOT . '/classes/sys/CBaseHandler.php';
class Page404 extends CBaseHandler{
	public $file_list;
	public function __construct() {
		$this->action404();
		parent::__construct();
	}
}
