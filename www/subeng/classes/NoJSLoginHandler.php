<?php
require_once APP_ROOT . '/classes/sys/CBaseHandler.php';
require_once APP_ROOT . '/classes/SpecialLoginHandler.php';

class NoJSLoginHandler extends CBaseHandler {
	private $_slh;
	public $uname;
	public $usname;
	public $rlogin;
	
	public function __construct($app) {
		$this->css[] = 'simple';
		$this->right_inner = 'nojs/loginform.tpl.php';
		parent::__construct($app);
		$this->_processRequest();
	}
	private function _processRequest() {
		switch ($this->_a_url[1]) {
			case 'nojslogin':
				$slh = new SpecialLoginHandler();
				if (!$slh->login(false)) {
					$this->errors[] = $this->lang['user_not_found'];
				} else {
					utils_302(WEB_ROOT . '/jsoff_login');
				}
				break;
			case 'nojsregister':
				$this->right_inner = 'nojs/regform.tpl.php';
				$slh = new SpecialLoginHandler();
				$msg = $slh->signup(false);
				if ($msg != $this->lang['reg_complete']) {
					$this->errors[] = $msg;
					cfr($this);
				} else {
					sess('reg_complete', $msg);
					utils_302(WEB_ROOT . '/jsoff_login');
				}
				break;
			case 'jsoff_register':
				$this->right_inner = 'nojs/regform.tpl.php';
				break;
			case 'jsoff_login':
				if (sess('reg_complete')) {
					$this->messages[] = sess('reg_complete');
					sess('reg_complete', 'unset');
				}
				break;
		}
	}
}
