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
			case $work_folder: 
				$this->layout = 'tpl/simple_page.master.tpl.php';
				$this->handler = $h = $this->_load('SimplePageHandler');
				return;
			case $work_folder . '/testdbmapping':
				$this->layout = 'tpl/simple_page.master.tpl.php';
				$this->handler = $h = $this->_load('TestDbMappingHandler');
				return;
			case $work_folder . '/jsoff_login':
			case $work_folder . '/jsoff_register':
				$this->layout = 'tpl/std/nojs.master.tpl.php';
				$this->handler = $h = $this->_load('NoJSLoginHandler');
				return;
			case $work_folder . '/nojslogin':
			case $work_folder . '/nojsregister':
				$this->layout = 'tpl/std/nojs.master.tpl.php';
				$this->handler = $h = $this->_load('NoJSLoginHandler');
				return;
		}
		parent::_route($url);
	}
}
