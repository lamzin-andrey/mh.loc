<?php
require_once APP_ROOT . '/classes/sys/CBaseHandler.php';
class TestDbMappingHandler extends CBaseHandler{
	public function __construct() {
		$this->css[] = 'simple';
		$this->js[] = 'simple';
		$this->right_inner = 'test_req_mapping.tpl.php';
		parent::__construct();
		$this->_processPost();
	}
	
	private function _processPost() {
		if (req('action') == 'add_test_user') {
			$data = db_mapPost('mapping');
			var_dump($_POST);
			var_dump($data); die;
		}
	}
}
