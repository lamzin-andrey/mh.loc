<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mysql.php';
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/baseapp.php';

/** Совместимость  */
class CApplication {
	static public $uid = 0;
	static public function setUid($uid) {
		static::$uid = $uid;
	}
	
	static public function getUid() {
		return static::$uid;
	}
}

/*  CREATE POST **/
class PostApp extends BaseApp{
	public function __construct() {
		$this->_actionPost();
	}
	/**
	 * @description запрос слова
	*/
	public function _actionPost() {
		$uid = 0;
		if (isset($_COOKIE[AUTH_COOKIE_NAME])) {
			$id = (int)dbvalue("SELECT id FROM secure_pad_users WHERE auth_id = '{$_COOKIE[AUTH_COOKIE_NAME]}'");
			if ($id) {
				$uid = $id;
				CApplication::setUid($id);
			} else {
				json_ok('error', 'Произошла какая-то ошибка, попробуйте обновить страницу');
			}
		} else {
			json_ok('error', 'Произошла какая-то ошибка, попробуйте обновить страницу');
		}
		
		
		$this->tsreq('iBody', 'body');
		$this->tsreq('iTitle', 'title');
		$this->ireq('iCurrentPostId', 'id');
		$message = $error = '';
		if (count($_POST)) {
			$this->table = 'secure_pad_posts';
			$error = $this->_validate();
			if (!$error) {
				if ($this->id) {
					$s = $this->updateQuery('id = ' . $this->id);
					query($s);
				} else {
					$s = $this->insertQuery();
					$this->id = query($s);
				}
				json_ok('message', 'Данные сохранены', 'id', $this->id, 'text', $this->body, 'title', $this->title);
			} else {
				$this->jsonError($error);
			}
		}
		return json_encode(['empty_post' => 1, 'error' => 'Не удалось вставить данные']); 
	}
	
	private function _validate() {
		if (!$this->title) {
			return "Не заполнено поле Заголовок";
		}
		if (!$this->body) {
			return "Не заполнено поле Текст";
		}
		return '';
	}
}
new PostApp();

