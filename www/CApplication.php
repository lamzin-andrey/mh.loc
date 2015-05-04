<?php
require_once dirname(__FILE__) . '/classes/sys/CBaseApplication.php';
class CApplication extends CBaseApplication {
	
	public function __construct() {
		$this->title("Базовое приложение", "Движок без названия");
		parent::__construct();
	}
	
	protected function _route($url) {
		$work_folder = WORK_FOLDER;
		switch ($url) {
			case $work_folder . '/simple':
				$this->layout = 'tpl/simple_page.master.tpl.php';
				$this->handler = $h = $this->_load('SimplePageHandler');
				return;
		}
		parent::_route($url);
	}
}