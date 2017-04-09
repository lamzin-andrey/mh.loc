<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mysql.php';
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/baseapp.php';
//require_once __DIR__ . '/captcha/captcha.php';

class LastPostApp extends BaseApp{
	public function __construct() {
		$this->_actionPost();
	}
	/**
	 * @description запрос слова
	*/
	public function _actionPost() {
		if (isset($_COOKIE[AUTH_COOKIE_NAME])) {
			$id = (int)dbvalue("SELECT id FROM secure_pad_users WHERE auth_id = '{$_COOKIE[AUTH_COOKIE_NAME]}'");
			if ($id) {
				$row = dbrow("SELECT id, title, body FROM secure_pad_posts WHERE uid = {$id} AND is_deleted = 0 ORDER BY id DESC LIMIT 1");
				if ($row) {
					json_ok('id', $row['id'], 'text', $row['body'], 'title', $row['title']);
				} else {
					json_ok('state', 'showform', 'error', 1);
				}
			} else {
				json_ok('error', 'Произошла какая-то ошибка, попробуйте обновить страницу');
			}
		}
		json_ok('error', 'Произошла какая-то ошибка, попробуйте обновить страницу');
	}
	
	
}
new LastPostApp();
