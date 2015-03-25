<?php
require_once APP_ROOT . '/classes/sys/CBaseHandler.php';
require_once APP_ROOT . '/classes/mail/SampleMail.php';
class LoginHandler extends CBaseHandler {
	public $_remindError;
	public $_remind_message;
	public function __construct($app) {
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
	
	private function _logout(){
		$_SESSION = array();
		utils_302(WEB_ROOT . '/');
	}
	
	private function _login() {
		$email = @$_POST['email'];
		$password = $this->_getHash(@$_POST["password"]);
		$sql_query = "SELECT u.id FROM users AS u
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
			print json_encode(array("success"=>'1'));
		} else {
			print json_encode(array("success"=>'0'));
		}
		exit;
	}
	/**
	 * @desc Регистрация пользователя
	**/
	private function _signup() {
		$lang = utils_getCurrentLang();
		$email = req('email');
		$pwd   = req('password');
		$pwd_c = req('pc');
		$name  = req('name');
		$sname = req('sname');
		
		if (isset($this->_app->reg_captcha)) {
			$enter = req('regfstr');
			if ($enter != sess('capcode')) {
				json_error('sError', $lang['code_is_not_valid']);
			}
		}
		
		if (!trim($email)) {
			json_error('sError', $lang['email_required']);
		}
		if (!checkMail($email)) {
			json_error('sError', $lang['email_is_not_valid']);
		}
		//die("SELECT id FROM users WHERE email = '{$email}'");
		$exists = dbvalue("SELECT id FROM users WHERE email = '{$email}'");
		if ($exists) {
			json_error('sError', $lang['email_already_exists']);
		}
		if (!trim($pwd)) {
			json_error('sError', $lang['password_required']);
		}
		if ($pwd != $pwd_c) {
			json_error('sError', $lang['password_different']);
		}
		$pwd = $this->_getHash($pwd);
		$name = str_replace("'", '&quot;', trim($name));
		$surname = str_replace("'", '&quot;', trim($sname));
		$email = str_replace("'", '&quot;', trim($email));
		$uid = CApplication::getUid();
		if (!$uid) {
			$datetime = now();
			$query = "INSERT INTO users (guest_id) VALUES (MD5('{$datetime}'))";
			$uid = query($query);
		}
		$sql_query = "UPDATE users SET name = '{$name}', surname = '{$surname}', email = '{$email}', pwd = '{$pwd}' WHERE id = {$uid}";
		//die($sql_query);
		query($sql_query, $nR, $aR);
		if ($aR) {
			json_ok('sError', $lang['reg_complete']);
		} else{
			json_error('sError', $lang['default_error']);
		}
	}
	/*
	 * 
	*/
	private function _getHash($s) {
		return md5(str_replace("'", '&quot;', trim($s)));
	}
	/**
	 * @desc Показываем форму восстановления пароля
	**/
	private function _getpwd() {
		
	}
	/**
	 * @desc Принимаем мыло с капчей и отправляем ссылку
	**/
	private function _sendRecoveryMail() {
		$lang = utils_getCurrentLang();
		$email = req('email');
		
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
	
		
		$row = dbrow("SELECT id, name, surname FROM users WHERE email = '{$email}'", $numRows);
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
				query("UPDATE users SET recovery_hash = '{$hash_recovery}', recovery_hash_created = '{$time}' WHERE id = {$uid}");
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
	private function _showResetPasswordForm() {
		$lang = utils_getCurrentLang();
		$hash = req('hash');
		if ($hash) {
			$_hash = substr($hash, 0, 32);
			if ($hash == $_hash) {
				$uid = (int)dbvalue("SELECT id FROM users WHERE recovery_hash = '{$hash}'");
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
	private function _resetPassword() {
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
		query("UPDATE users SET pwd = '{$pwd}', recovery_hash = '' WHERE id = {$uid}");
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
	private function _showSuccess() {
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
	
}
