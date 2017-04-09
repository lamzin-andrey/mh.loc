<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mysql.php';
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/baseapp.php';

/*  SIGNIN **/
class SignInApp extends BaseApp{
	public function __construct() {
		$this->_actionPost();
	}
	/**
	 * @description запрос слова
	*/
	public function _actionPost() {
		$this->tsreq('iLlogin', 'login');
		$this->tsreq('iLpwd', 'pwd');
		
		$message = $error = '';
		if (count($_POST)) {
			$this->table = 'secure_pad_users';
			$error = $this->_validate();
			if (!$error) {
				$s = "SELECT * FROM {$this->table} WHERE login = '{$this->login}' LIMIT 1";
				$row = dbrow($s);
				if ($row) {
					$sPwd = $row['pwd'];
					$pwd = md5($this->pwd);
					if ($sPwd == $pwd) {
						setcookie(AUTH_COOKIE_NAME, $row['auth_id'], time() + 365 * 24 * 3600, '/');
						$rowPost = dbrow("SELECT id, title, body FROM secure_pad_posts WHERE uid = {$row['id']} AND is_deleted = 0 ORDER BY id DESC LIMIT 1");
						if ($rowPost) {
							json_ok('id', $rowPost['id'], 'text', $rowPost['body'], 'title', $rowPost['title']);
						}
						json_ok('message', 'Создайте новую запись');
					} else {
						$this->jsonError('Пользователь с таким логином и паролем не найден');
					}
				} else {
					json_ok('error', 'Пользователь с таким логином не найден');
				}
			} else {
				$this->jsonError($error);
			}
		}
		return json_encode(['empty_post' => 1, 'error' => 'Не удалось вставить данные']); 
	}
	
	private function _validate() {
		if (!$this->login) {
			return "Не заполнено поле Логин";
		}
		if (!$this->pwd) {
			return "Не заполнено поле Пароль";
		}
		return '';
	}
}
new SignInApp();

