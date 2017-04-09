<?php
require_once APP_ROOT . '/classes/sys/CBaseHandler.php';
require_once APP_ROOT . '/classes/mail/SampleMail.php';
class LoginHandler extends CBaseHandler {
	public $_remindError;
	public $_remind_message;
	public $table = 'users';
	public function __construct($app, $table = 'users') {
		$this->table = $table;
		$this->_app = $app;
		$this->left_inner = 'main_tasklist.tpl.php';
		$this->right_inner = 'std/remind_inner.tpl.php';
		$this->css[] = 'remind';
		switch (@$_REQUEST["action"]) {
			case "login":
				$this->_login();
				break;
			case "logout":
				$this->_logout();
				break;
			case "signup":
				$this->_signup();
				break;
			case "getpwd":
				$this->_getpwd();
				break;
			case "sendmail":
				$this->_sendRecoveryMail();
				break;
			case "hash":
				$this->_showResetPasswordForm();
				break;
			case "recovery":
				$this->_resetPassword();
				break;
			case "success":
				$this->_showSuccess();
				break;
			default:
				if (!@$_SESSION["uid"]) {
					utils_302(WEB_ROOT . '/');
				}
		}
	}
	
	protected function _logout(){
		$_SESSION = array();
		utils_302(WEB_ROOT . '/');
	}
	
	protected function _login($use_json = true) {
		$email = req('email');
		$email = db_safeString($email);
		$password = $this->_getHash(@$_POST["password"]);
		$sql_query = "SELECT u.id FROM {$this->table} AS u
						WHERE u.email = '$email' AND u.pwd = '$password'";
		$data = query($sql_query, $nR);
		$id = 0;
		if ($nR) {
			$row = $data[0];
			$id = $row['id'];
		}
		if ($id) {
			$_SESSION["authorize"] = true;
			$_SESSION["uid"] = $id;
			$_SESSION["email"] = $email;
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
		$email = req('email');
		$pwd   = req('password');
		$pwd_c = req('pc');
		$name  = req('name');
		$sname = req('sname');
		
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
			//json_error('sError', $lang['email_required']);
			return $this->_getError($use_json, $lang['email_required']);
		}
		if (!checkMail($email)) {
			//json_error('sError', $lang['email_is_not_valid']);
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
	/*
	 * 
	*/
	protected function _getHash($s) {
		return md5(str_replace("'", '&quot;', trim($s)));
	}
	/**
	 * @desc Показываем форму восстановления пароля
	**/
	protected function _getpwd() {
		
	}
	/**
	 * @desc Принимаем мыло с капчей и отправляем ссылку
	**/
	protected function _sendRecoveryMail() {
		$lang = utils_getCurrentLang();
		$email = req('email');
		db_safeString($email);
		//if (isset($this->_app->reg_captcha)) {
			$enter = req('regfstr');
			
			if ($enter != sess('capcode')) {
				//json_error('sError', $lang['code_is_not_valid']);
				$this->_remindError = $lang['code_is_not_valid'];
				return;
			}
		//}
		
		if (!trim($email)) {
			//json_error('sError', $lang['email_required']);
			$this->_remindError = $lang['email_required'];
			return;
		}
		if (!checkMail($email)) {
			//json_error('sError', $lang['email_is_not_valid']);
			$this->_remindError = $lang['email_is_not_valid'];
			return;
		}
		
		$row = dbrow("SELECT id, name, surname FROM {$this->table} WHERE email = '{$email}'", $numRows);
		if ($numRows) {
			$time = time();
			$email = trim($email);
			$hash_recovery = md5("{$email}{$time}");
			$uid = (int)$row['id'];
			$name = $row['name'] ? $row['name'] : '';
			if ($row['surname']) {
				$name .= ' ' . $row['surname'];
			}
			if (!$name) {
				$name = $email;
			}
			
			//sendMail
			$mailer = new SampleMail();
			$mailer->setSubject("Восстановление пароля на firstcode.ru");
			$mailer->setAddressFrom(array("profile@firstcode.ru"=>"Firstcode.ru"));
			$mailer->setAddressTo(array($email=>$name));
			

			//sample mail
			$mailer->setPlainText("Здравствуйте, {$name}!
			
			Вы или кто-то другой запросили восстановление пароля на сайте http://firstcode.ru
			Если это были не вы, проигнорируйте это письмо.
			
			Для восстановления пароля пройдите <a href=\"http://firstcode.ru/remind?action=hash&hash={$hash_recovery}\">по ссылке</a>
			
			Это письмо сгенерировано автоматически, отвечать на него не надо.
			", array());
			$r = $mailer->send();
			//var_dump($r);
			if ($r) {	
				//update hash in db
				query("UPDATE {$this->table} SET recovery_hash = '{$hash_recovery}', recovery_hash_created = '{$time}' WHERE id = {$uid}");
				$this->_remind_message = $lang['success_send_mail'];
			} else {
				$this->_remindError = $lang['fail_send_mail'];
			}
		} else {
			$this->_remindError = $lang['user_with_email_not_found'];
		}
		
	}
	/**
	 * @desc Смотрим, если хеш есть, показываем форму для сброса пароля
	**/
	protected function _showResetPasswordForm() {
		$lang = utils_getCurrentLang();
		$hash = req('hash');
		if ($hash) {
			$_hash = substr($hash, 0, 32);
			if ($hash == $_hash) {
				$uid = (int)dbvalue("SELECT id FROM {$this->table} WHERE recovery_hash = '{$hash}'");
				if ($uid) {
					$this->right_inner = 'std/recovery_password_inner.tpl.php';
					@session_start();
					sess('recovery_hash', $hash);
					sess('recovery_uid', $uid);
					return;
				}
			}
		}
		$this->_remindError = $lang['bad_hash'];
		$this->_remind_message = $lang['try_remind_again'];
	}
	/**
	 * @desc Сбросить пароль
	**/
	protected function _resetPassword() {
		$this->right_inner = 'std/recovery_password_inner.tpl.php';
		$lang = utils_getCurrentLang();
		@session_start();
		$hash = sess('recovery_hash');
		$uid = sess('recovery_uid');
		if (!$uid) {
			$this->_remindError = $lang['bad_hash'];
			$this->_remind_message = $lang['try_remind_again'];
			return;
		}
		$pwd = req('remindpassword');
		$pwd_c = req('remind_password_confirm');
		if (!trim($pwd)) {
			//json_error('sError', $lang['password_required']);
			$this->_remindError = $lang['password_required'];
			return;
		}
		if ($pwd != $pwd_c) {
			//json_error('sError', $lang['password_different']);
			$this->_remindError = $lang['password_different'];
			return;
		}
		$pwd = $this->_getHash($pwd);
		query("UPDATE {$this->table} SET pwd = '{$pwd}', recovery_hash = '' WHERE id = {$uid}");
		$this->_remind_message = $lang['success_updated_password'];
		//$this->right_inner = 'std/recovery_password_success_inner.tpl.php';
		unset( $_SESSION['recovery_hash'] );
		unset( $_SESSION['recovery_uid'] );
		sess('remind_success', 1);
		utils_302('/remind?action=success');
	}
	/**
	 * @desc Показать страницу Успех при восстановлении пароля
	**/
	protected function _showSuccess() {
		$lang = utils_getCurrentLang();
		@session_start();
		if (sess('remind_success')) {
			unset( $_SESSION['remind_success'] );
			$this->_remind_message = $lang['success_updated_password'];
			$this->right_inner = 'std/recovery_password_success_inner.tpl.php';
		} else {
			$this->_remindError = $lang['bad_hash'];
			$this->_remind_message = $lang['try_remind_again'];
		}
	}
	
	protected function _getError($use_json, $msg) {
		if ($use_json) {
			json_error('sError', $msg);
		}
		return $msg;
	}
	
	protected function _getMessage($use_json, $msg, $key = 'sError') {
		if ($use_json) {
			json_ok($key, $msg);
		}
		return $msg;
	}
	/**
	 * @desc checked length [6-12], only \N and [A-Z], and use ever from it
	 * @return bool
    */
	protected function _validPassword($pwd) {
		if (strlen($pwd) < 6 && strlen($pwd) > 12) {
			return false;
		}
		$letters = 'abcdefghijklmnopqrstuvwxyz';
		$upletters = strtoupper($letters);
		$nums = '0123456789';
		$all = $upletters . $letters . $nums;
		$num_exists = false;
		$lo_exists = false;
		$up_exists = false;
		for ($i = 0; $i < strlen($pwd); $i++) {
			$ch = $pwd[$i];
			if (strpos($all, $ch) === false) {
				return false;
			}
			if (!$lo_exists) {
				if (strpos($letters, $ch) !== false) {
					$lo_exists = true;
				}
			}
			if (!$up_exists) {
				if (strpos($upletters, $ch) !== false) {
					$up_exists = true;
				}
			}
			if (!$num_exists) {
				if (strpos($nums, $ch) !== false) {
					$num_exists = true;
				}
			}
		}
		if (!$lo_exists || !$up_exists || !$num_exists) {
			return false;
		}
		return true;
	}
}
