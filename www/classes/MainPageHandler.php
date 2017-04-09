<?php
require_once APP_ROOT . '/classes/sys/CBaseHandler.php';
class MainPageHandler extends CBaseHandler{
	public function __construct() {
		$this->left_inner = 'main_tasklist.tpl.php';
		if (!CApplication::userIsAuth()) {
			$this->right_inner = 'main_promo.tpl.php';
		} else {
			utils_302(WEB_ROOT . '/sts');
		}
		$this->css[] = 'promo';
		parent::__construct();
	}
}
