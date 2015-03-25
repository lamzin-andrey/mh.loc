<?php
require_once dirname(__FILE__) . '/utils.php';
require_once dirname(__FILE__) . '/mysql.php';
require_once dirname(__FILE__) . '/../CViewHelper.php';
class CBaseApplication {
	public $handler = null;
	public $lang = array();
	public $user_email;
	public $user_name;
	public $user_surname;
	public $role = 0;
	public $base_url;
	public $layout = 'tpl/master.tpl.php';
	protected $_title = '';
	protected $_base_title = '';
	protected $_title_separator = '|';
	//public $reg_captcha = true;
	//public $comm_captcha = true;
	
	public function __construct() {
		@session_start();
		@date_default_timezone_set("Europe/Moscow");
		$this->lang = utils_getCurrentLang();
		$this->_loadAuthUserData();
		$this->_ajaxHandler();
		//TODO роутер
		$aUrl = explode('?', $_SERVER['REQUEST_URI']);
		$url = '/' . trim($aUrl[0], '/');
		$this->base_url = $url;
		//die("26 W_F = '$work_folder', url = '$url'");
		$this->_route($url);
	}
	protected function _route($url) {
		$work_folder = WORK_FOLDER;
		switch ($url) {
			case '/':
				$this->_promoPageActions();
				break;
			case '/login':
				$this->_loginActions();
				break;
			case $work_folder . '/remind':
				$this->_remindPasswordActions();
				break;
			case $work_folder . '/signin':
				$this->_registrationAction();
				break;
			
			default:
				$this->_404();
		}
	}
	/**
	 * @desc Обработка возможных действий при регистрации и авторизации
	**/
	protected function _loginActions() {
		$this->handler = $h = $this->_load('LoginHandler');
	}
	/**
	 * @desc Обработка возможных действий при регистрации и авторизации
	**/
	protected function _remindPasswordActions() {
		$this->handler = $h = $this->_load('LoginHandler');
	}
	
	/**
	 * @desc Обработка 404
	**/
	protected function _404() {
		$this->handler = $h = $this->_load('Page404');
	}
	/**
	 * @desc Обработка возможных действий на главной странице
	**/
	protected function _promoPageActions() {
		$this->handler = $h = $this->_load('MainPageHandler');
	}
	/**
	 * @desc Обработка возможных действий на странице
	 * @param $class_name - Имя класса, который надо подгрузить
	 * @param $access_level = 0 - минимально необходимые права
	**/
	protected function _load($class_name, $level = 0) {
		if ($this->role < $level) {
			utils_302(WEB_ROOT);
			return;
		}
		$file = APP_ROOT . '/classes/' . $class_name . '.php';
		if (file_exists($file)) {
			include_once($file);
			return new $class_name($this);
		} else {
			throw new Exception('Класс '  . $class_name . ' не найден там, где он ожидался');
		}
	}
	/**
	 * @desc альтернатива @
	 * @param v - имя переменной
	 * @param varname - имя переменной
	**/
	protected function _req($v, $varname = 'REQUEST') {
		$data = $_REQUEST;
		switch ($varname) {
			case 'POST':
			$data = $_POST;
				break;
			case 'GET':
				$data = $_GET;
				break;
		}
		if (isset($data[$v])) {
			return $data[$v];
		}
		return null;
	}
	/**
	 * 
	*/
	static public function getUid() {
		if ((int)sess('uid')) {
			return (int)sess('uid');
		}
		if (USE_GUID_SESSION == true) {
			if ((int)sess('guid')) {
				return (int)sess('guid');
			}
			if (trim(a($_COOKIE, 'guest_id'))) {
				$guid = trim(a($_COOKIE, 'guest_id'));
				$guid = dbvalue("SELECT id FROM users WHERE guest_id = '{$guid}'");
				if ($guid) {
					sess('guid', $guid);
				}
				return $guid;
			}
		}
		return 0;
	}
	/**
	 * @desc Получить мыло имя и фамилию неанонимного пользователя
	*/
	protected function _loadAuthUserData() {
		if ($uid = (int)sess('uid')) {
			$data = dbrow("SELECT id, email, name, surname, role FROM users WHERE id = '{$uid}'", $nR);
			//$guid = 0;
			if ($nR) {
				$this->user_email = $data['email'];
				$this->user_name = $data['name'];
				$this->user_surname = $data['surname'];
				$this->role = $data['role'];
			}
		}
	}
	/**
	 * @desc Обработка аякс запросов
	**/
	protected function _ajaxHandler() {
		$action = $this->_req('action', 'POST');
		$lang = $this->lang;
		switch ($action) {
			case 'getGuid':
				$this->_generateGuid();
				break;
		}
	}
	/**
	 * @desc Создать уникальный идентификатор анонимного пользователя
	**/
	protected function _generateGuid() {
		if (USE_GUID_SESSION != true) {
			json_ok('guid', 0);
		}
		$datetime = now();
		$ip = $_SERVER['REMOTE_ADDR'];
		$query = "INSERT INTO users (guest_id) VALUES (MD5('{$ip}{$datetime}'))";
		$uid = query($query);
		$query = "SELECT guest_id FROM users WHERE id = {$uid}";
		$guid = dbvalue($query);
		json_ok('guid', $guid);
	}
	/**
	 * @return true если пользователь залогинен
	*/
	static public function userIsAuth() {
		return (sess('uid') ? true: false);
	}
	/**
	 * @return title
	*/
	public function title($title = '', $base_title = '', $title_separator = '') {
		$s = '';
		if ($title) {
			$this->_title = $title;
		}
		if ($base_title) {
			$this->_base_title = $base_title;
		}
		if ($title_separator) {
			$this->_title_separator = $title_separator;
		}
		$s = $this->_title;
		if ($this->_base_title) {
			$s = $this->_base_title . ' ' . $this->_title_separator . $this->_title;
		}
		return $s;
	}
}
