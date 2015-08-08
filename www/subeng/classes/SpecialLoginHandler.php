<?php
require_once APP_ROOT . '/classes/LoginHandler.php';
require_once APP_ROOT . '/classes/mail/SampleMail.php';

class SpecialLoginHandler extends LoginHandler {
	
	public function __construct() {
		//parent::__construct($app, $table);
	}
	protected function _login($use_json = true) {
		$login = req('login');
		$password = $this->_getHash(@$_POST["password"]);
		$sql_query = "SELECT u.id FROM {$this->table} AS u
						WHERE u.email = '$login' AND u.pwd = '$password'";
		$data = query($sql_query, $nR);
		$id = 0;
		if ($nR) {
			$row = $data[0];
			$id = $row['id'];
		}
		if ($id) {
			$_SESSION["authorize"] = true;
			$_SESSION["uid"] = $id;
			$_SESSION["login"] = $login;
			if ($use_json) {
				print json_encode(array("success"=>'1'));
				exit;
			} else {
				return 1;
			}
		} else {
			if ($use_json) {
				print json_encode(array("success"=>'0'));
				exit;
			} else {
				return 0;
			}
		}
	}
	/**
	 * @desc Регистрация пользователя
	 * @param $use_json = true
	**/
	protected function _signup($use_json = true) {
		$lang = utils_getCurrentLang();
		$email = req('rlogin');
		//$phone = req('phone');
		$pwd   = req('password');
		$pwd_c = req('password_confirm');
		$name  = req('uname');
		$sname = req('usname');
		
		//$phone = AdvLib::preparePhone($phone);
		db_safeString($email);
		db_safeString($pwd);
		db_safeString($pwd_c);
		db_safeString($name);
		db_safeString($sname);
		
		if (isset($this->_app->reg_captcha)) {
			$enter = req('regfstr');
			if ($enter != sess('capcode')) {
				return $this->_getError($use_json, $lang['code_is_not_valid']);
			}
		}
		if (!trim($email)) {
			return $this->_getError($use_json, $lang['email_required']);
		} else {
			/*$uid = dbvalue("SELECT id FROM {$this->table} WHERE email = '{$email}'");
			if (!$uid) {
				$this->_getError($use_json, $lang['email_already_exists']);
			}*/
		}
		
		if (trim($email) && !checkMail($email)) {
			return $this->_getError($use_json, $lang['email_is_not_valid']);
		}
		$exists = dbvalue("SELECT id FROM {$this->table} WHERE email = '{$email}'");
		if ($exists) {
			//json_error('sError', $lang['email_already_exists']);
			return $this->_getError($use_json, $lang['email_already_exists']);
		}
		if (!trim($pwd)) {
			//json_error('sError', $lang['password_required']);
			return $this->_getError($use_json, $lang['password_required']);
		}
		if ($pwd != $pwd_c) {
			//json_error('sError', $lang['password_different']);
			return $this->_getError($use_json, $lang['password_different']);
		}
		if (!$this->_validPassword($pwd)) {
			//json_error('sError', $lang['password_different']);
			return $this->_getError($use_json, $lang['password_bad_symbols']);
		}
		$pwd = $this->_getHash($pwd);
		$name = str_replace("'", '&quot;', trim($name));
		$surname = str_replace("'", '&quot;', trim($sname));
		$email = str_replace("'", '&quot;', trim($email));
		$uid = CApplication::getUid();
		if (!$uid) {
			$datetime = now();
			$query = "INSERT INTO {$this->table} (guest_id) VALUES (MD5('{$datetime}'))";
			$uid = query($query);
		}
		$sql_query = "UPDATE {$this->table} SET name = '{$name}', surname = '{$surname}', email = '{$email}', pwd = '{$pwd}' WHERE id = {$uid}";
		//die($sql_query);
		query($sql_query, $nR, $aR);
		if ($aR) {
			//json_ok('sError', $lang['reg_complete']);
			return $this->_getMessage($use_json, $lang['reg_complete']);
		} else{
			//json_error('sError', $lang['default_error']);
			return $this->_getError($use_json, $lang['default_error']);
		}
	}
	
	public function signup($use_json = false) {
		return $this->_signup($use_json);
	}
	public function login($use_json = false) {
		return $this->_login($use_json);
	}
	
}
