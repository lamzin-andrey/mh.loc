<?php
//!! token при использовании метода $p->submitForm передавать НЕ ОБЯЗАТЕЛЬНО! FIX!
require_once dirname (__FILE__) . '/tools/index.php';
$p->defaultHost = 'http://64.loc';
$_SERVER['HTTP_HOST'] = $p->defaultHost;
define('APP_ROOT', dirname (__FILE__) . '/../www');

require_once dirname (__FILE__) . '/../www/config.php';
require_once dirname (__FILE__) . '/../www/classes/sys/mysql.php';

function expectText($r, $text, $label = '') {
	global $req, $p;
	if (!$label) {
		$label = $text;
	}
	expect($p->hasText($r->responseText, $text), true, $label);
}

class TestMessages {
	public $p;
	public $req;
	public $phone = '89187353620';
	public $email = 'lamzin50@mail.ru';
	public $password = 'Qw123456';
	public $lastToken;
	public $recipientNick = 'RoNickTest';
	public $recipientPhone = '89187353621';	
	
	private $userId;
	private $recipientId;
	
	function __construct() {
		global $p, $req;
		$this->p = $p;
		$this->req = $req;
		query("DELETE FROM users WHERE phone = '{$this->phone}'");
		query("DELETE FROM users WHERE phone = '{$this->recipientPhone}'");
		$this->registerRecipient();
		$this->clearSession();
		$this->registerUser();
		$this->login();
		
		$this->sendNoExistsUser();
		$this->sendExistsUser();
		$this->offRateBan();
		$this->sendWithAttach();
		$this->offRateBan();
		$this->attachOne();
		$this->offRateBan();
		$this->attachMulty();
		$this->offRateBan();
		$this->sendWithoutRecipient();
		$this->offRateBan();
		$this->sendWithoutSubject();
		$this->offRateBan();
		$this->sendWithoutBody();
		$this->offRateBan();
		
		$this->sendAttachWithoutRecipient();
		$this->offRateBan();
		$this->sendAttachWithoutSubject();
		$this->offRateBan();
		$this->sendAttachWithoutBody();
		$this->offRateBan();
		
		$this->sendRate();
		$this->sendRateWithCaptcha();
		$this->sendWithAllowPauseRate();
		
		$this->clearMessages();
		query("DELETE FROM users WHERE phone = '{$this->phone}'");
		query("DELETE FROM users WHERE phone = '{$this->recipientPhone}'");
	}
	
	public function clearMessages() {
		//get data
		$rows = query("SELECT id, ai_attach_list FROM messages WHERE 
			ito_id = {$this->recipientId}
		OR	ito_id = {$this->userId}
		OR	ifrom_id = {$this->userId}
		OR	ifrom_id = {$this->recipientId}
			");
		$ids = array();
		$files = array();
		foreach ($rows as $row) {
			$ids[] = $row['id'];
			$fids = explode(',', $row['ai_attach_list']);
			foreach ($fids as $cfid) {
				$fid = (int)$cfid;
				if ($fid) {
					$files[$fid] = $fid;
				}
			}
		}
		//clear files
		if (count($files)) {
			$files = join(',', $files);
			query("DELETE FROM attach WHERE id IN ({$files})");
		}
		//clear messages
		if (count($ids)) {
			$ids = join(',', $ids);
			query("DELETE FROM messages WHERE id IN ({$ids})");
		}
	}
	
	public function getToken($requestResponse, $dbg = false) {
		$r = $requestResponse;
		$html = $r->responseText;
		if ($dbg) {
			die($html);
		}
		$dom = new DOMDocument();
		$dom->validateOnParse = false;
		@$dom->loadHtml($html);
		$forms = $dom->getElementsByTagName('form');
		$token = '';
		for ($i = 0; $i < $forms->length; $i++) {
			$form = $forms->item($i);
			$inputs = $form->getElementsByTagName('input');
			for ($j = 0; $j < $inputs->length; $j++) {
				$input = $inputs->item($j);
				if ($input->hasAttribute('name') && $input->getAttribute('name') == 'token') {
					$token = $input->getAttribute('value');
					if ($token) {
						break;
					}
				}
			}
			if ($token) {
				break;
			}
		}
		if (!$token && $this->lastToken) {
			$token = $this->lastToken;
		}
		$this->lastToken = $token;
		return $token;
	}

	public function getCaptchaCode($dbg = false) {
		$p = $this->p;
		if ($p->request) {
			$r = $p->request->execute($p->defaultHost);
			$token = $this->getToken($r, $dbg);
			if (!$token) {
				expect($token != '', true, 'Cpacode: Token not found');
			}
			$p->request->execute($p->defaultHost . '/img/random');
			$url = $p->defaultHost . '/capcode';
			$r = $p->request->execute($url, array('cc' => 'p51rzg1lx', 'token' => $token));
			return $r->responseText;
		}
		return '';
	}
	

	public function agree() {
		global $req, $p;
		if ($req === null) {
			die('Ura');
		}
		$r = $req->execute($p->defaultHost . '/r');
		$lic = $p->getLink($r->responseText, 'Я согласен с условиями, перейти к регистрации');
		$r = $req->execute($p->defaultHost . $lic, array(), $p->defaultHost . '/agreement');
		$p->request = $req;
		return $this->getToken($r);
	}

	//Регистрация пользователя
	public function registerUser() {
		global $p;
		$token = $this->agree();
		$phone = $this->phone;
		$email = $this->email;

		$r = $p->submitForm($p->defaultHost . '/r', 'Регистрация', array(
			'uname' => 'Tester800',
			'usname' => 'Tester800',
			'nick' => 'Tester800',
			'rlogin' => $phone,
			'email' => $email,
			'password' => $this->password,
			'agree' => 'On',
			'password_confirm' => 'Qw123456',
			'token' => $token
		));
		expect($p->hasText($r->responseText, 'Введите символы с изображения'), true, 'После отправки данных о регистрации показывается  каптча');

		$row = dbrow("SELECT * FROM users WHERE phone = '{$phone}'", $n);
		expect($n, 1, 'Record was append');
		expect($row['is_active'], 0, 'user no active');
		query("UPDATE users SET is_active = 1 WHERE phone = '{$phone}'");
		$row = dbrow("SELECT * FROM users WHERE phone = '{$phone}'", $n);
		expect($row['is_active'], 1, 'user now active');
		$this->lastToken = $token;
		$this->userId = $row['id'];
		return $r;
	}
	
	//Регистрация получателя
	public function registerRecipient() {
		global $p;
		$this->agree();

		$r = $p->submitForm($p->defaultHost . '/r', 'Регистрация', array(
			'uname' => $this->recipientNick,
			'usname' => $this->recipientNick,
			'nick' => $this->recipientNick,
			'rlogin' => $this->recipientPhone,
			'email' => $this->recipientNick . '@mail.ru',
			'password' => $this->password,
			'agree' => 'On',
			'password_confirm' => 'Qw123456'
		));
		expect($p->hasText($r->responseText, 'Введите символы с изображения'), true, 'После отправки данных о регистрации показывается  каптча');

		$row = dbrow("SELECT * FROM users WHERE phone = '{$this->recipientPhone}'", $n);
		expect($n, 1, 'Record was append');
		expect($row['is_active'], 0, 'user no active');
		query("UPDATE users SET is_active = 1 WHERE phone = '{$this->recipientPhone}'");
		$row = dbrow("SELECT * FROM users WHERE phone = '{$this->recipientPhone}'", $n);
		expect($row['is_active'], 1, 'user now active');
		$this->recipientId = $row['id'];
		return $r;
	}
	
	public function setUserActive($phone) {
		query("UPDATE users SET is_active = 1 WHERE phone = '{$phone}'");
		$row = dbrow("SELECT * FROM users WHERE phone = '{$phone}'", $n);
		expect($row['is_active'], 1, 'user with phone = '. $phone .' is active');
	}
	public function setUserInactive($phone) {
		query("UPDATE users SET is_active = 0 WHERE phone = '{$phone}'");
		$row = dbrow("SELECT * FROM users WHERE phone = '{$phone}'", $n);
		expect($row['is_active'], 0, 'user with phone = '. $phone .' NO active');
	}
	
	public function sendNoExistsUser() {
		$login = 'NO_EXISTS777444_TEST';
		$r = $this->_send($login);
		expectText($r, 'Получатель '.$login.' не найден на нашем сайте. Вы можете попробовать связаться с ним, указав его email', 'Нельзя отправить пользователю, которого нет');
		$this->setUserInactive($this->recipientPhone);
		$r = $this->_send($this->recipientNick);
		expectText($r, 'Получатель '.$this->recipientNick.' не найден на нашем сайте. Вы можете попробовать связаться с ним, указав его email', 'Нельзя отправить пользователю, аккаунт которого не активен');
	}
	
	public function sendExistsUser() {
		$this->setUserActive($this->recipientPhone);
		$r = $this->_send($this->recipientNick);
		expectText($r, 'Сообщение отправлено');
	}
	
	public function sendRate() {
		$this->setUserActive($this->recipientPhone);
		$r = $this->_send($this->recipientNick);
		expectText($r, 'Сообщение отправлено');
		$r = $this->_send('andrey');
		expectText($r, 'Введите символы с изображения');
		
		//get cap_ban_expire
		$t1 = dbvalue("SELECT cap_ban_expire FROM users WHERE id = {$this->userId}");
		$r = $this->_send('andrey', 'SIGN');
		expectText($r, 'Введите символы с изображения');
		//get cap_ban_expire
		$t2 = dbvalue("SELECT cap_ban_expire FROM users WHERE id = {$this->userId}");
		//compare
		expect( (strtotime($t2) >  strtotime($t1)), true, "{$t2} > {$t1}");
		$this->offRateBan();
	}
	public function sendRateWithCaptcha() {
		$this->setUserActive($this->recipientPhone);
		$r = $this->_send($this->recipientNick);
		expectText($r, 'Сообщение отправлено');
		
		$r = $this->_send('andrey');
		expectText($r, 'Введите символы с изображения');
		
		$p = $this->p;
		$data = array(
			'display_user' => 'andrey',
			'subject' => 'subject',
			'body'    => 'text',
			'num'     => $this->getCaptchaCode()
		);
		$r = $p->submitForm($p->defaultHost . '/a', 'Отправить', $data);
		expectText($r, 'Сообщение отправлено', 'Сообщение успешно отправлено после ввода каптчи "' . $data['num'] . '"');
		$this->offRateBan();
	}
	/**
	 * @description Отправляет два сообщения , выдержав необходимуб паузу
	*/
	public function sendWithAllowPauseRate() {
		$this->setUserActive($this->recipientPhone);
		$r = $this->_send($this->recipientNick);
		expectText($r, 'Сообщение отправлено');
		sleep(SEND_MSG_ALLOW_RATE);
		$r = $this->_send($this->recipientNick);
		expectText($r, 'Сообщение отправлено', 'Сообщение успешно отправлено после паузы в ' .SEND_MSG_ALLOW_RATE . ' секунд');
		$this->offRateBan();
	}
	
	
	public function sendWithoutRecipient() {
		$this->setUserActive($this->recipientPhone);
		
		$p = $this->p;
		$data = array(
			//'display_user' => $this->recipientNick,
			'subject' => 'HEllo world',
			'body'    => 'Unknown recipienped'
		);
		$r = $p->submitForm($p->defaultHost . '/a', 'Отправить', $data);
		expectText($r, 'Неизвестный получатель');
	}
	public function sendWithoutSubject() {
		$this->setUserActive($this->recipientPhone);
		$p = $this->p;
		$data = array(
			'display_user' => $this->recipientNick,
			//'subject' => 'HEllo world',
			'body'    => 'Unknown recipienped'
		);
		$r = $p->submitForm($p->defaultHost . '/a', 'Отправить', $data);
		expectText($r, 'Поле "Тема" обязательно для заполнения');
	}
	public function sendWithoutBody() {
		$this->setUserActive($this->recipientPhone);
		$p = $this->p;
		$data = array(
			'display_user' => $this->recipientNick,
			'subject' => 'HEllo world',
			//'body'    => 'Unknown recipienped'
		);
		$r = $p->submitForm($p->defaultHost . '/a', 'Отправить', $data);
		expectText($r, 'Поле "Текст сообщения" обязательно для заполнения');
	}
	
	public function sendAttachWithoutRecipient() {
		$this->setUserActive($this->recipientPhone);
		
		$p = $this->p;
		$data = array(
			//'display_user' => $this->recipientNick,
			'subject' => 'HEllo world',
			'body'    => 'Unknown recipienped',
			'attach' => '@' . dirname(__FILE__) . '/res/t.png'
		);
		$r = $p->submitForm($p->defaultHost . '/a', 'Отправить', $data);
		expectText($r, 'Неизвестный получатель');
	}
	public function sendAttachWithoutSubject() {
		$this->setUserActive($this->recipientPhone);
		$p = $this->p;
		$data = array(
			'display_user' => $this->recipientNick,
			//'subject' => 'HEllo world',
			'body'    => 'Unknown recipienped',
			'attach' => '@' . dirname(__FILE__) . '/res/t.png'
		);
		$r = $p->submitForm($p->defaultHost . '/a', 'Отправить', $data);
		expectText($r, 'Поле "Тема" обязательно для заполнения');
	}
	public function sendAttachWithoutBody() {
		$this->setUserActive($this->recipientPhone);
		$p = $this->p;
		$data = array(
			'display_user' => $this->recipientNick,
			'subject' => 'HEllo world',
			//'body'    => 'Unknown recipienped',
			'attach' => '@' . dirname(__FILE__) . '/res/t.png'
		);
		$r = $p->submitForm($p->defaultHost . '/a', 'Отправить', $data);
		expectText($r, 'Поле "Текст сообщения" обязательно для заполнения');
	}
	
	public function sendWithAttach() {
		$this->setUserActive($this->recipientPhone);
		$r = $this->_send($this->recipientNick, 'with attach', 'message with attach', '@' . dirname(__FILE__) . '/res/t.png');
		expectText($r, 'Сообщение отправлено');
	}
	public function attachOne($message_id = 0) {
		$this->setUserActive($this->recipientPhone);
		
		$p = $this->p;
		$data = array(
			'display_user' => $this->recipientNick,
			'subject' => 'Hioho',
			'body'    => 'XioXao popopoposdd',
			'attach'    => '@' . dirname(__FILE__) . '/res/91.pdf',
			'file'    => 1
		);
		$tail = '/a';
		if ($message_id) {
			$tail = '/a?i=' . $message_id;
		}
		//echo 'send attach on ' . $p->defaultHost . $tail."\n\n";
		$r = $p->submitForm($p->defaultHost . $tail, 'Добавить файл', $data);
		expectText($r, '91.pdf', 'Вложение приложено');
	}
	public function attachMulty() {	
		$p = $this->p;
		$this->attachOne();
		$uid = dbvalue("SELECT id FROM users WHERE phone = '{$this->phone}'");
		$lastMessage = dbrow("SELECT * FROM messages WHERE ifrom_id = {$uid} ORDER BY id DESC LIMIT 1");
		expect($lastMessage['itype'], 0, 'Последнее отправленное has draft status');
		$a = explode(',', $lastMessage['ai_attach_list']);
		expect(count($a), 1, 'Последнее отправленное has one attach');
		$lastMid = $lastMessage['id'];
		
		$data = array(
			'display_user' => $this->recipientNick,
			'subject' => 'Hioho',
			'body'    => 'XioXao popopoposdd',
			'attach'    => '@' . dirname(__FILE__) . '/res/t.png'
		);
		$r = $p->submitForm($p->defaultHost . '/a?i=' . $lastMid, 'Отправить', $data);
		
		$lastMessage = dbrow("SELECT * FROM messages WHERE id = {$lastMid}");
		expect($lastMessage['itype'], 1, 'Последнее отправленное has send status');
		$a = explode(',', $lastMessage['ai_attach_list']);
		expect(count($a), 2, 'Последнее отправленное has two attach');
		
		$this->attachOne();
		$lastMessage = dbrow("SELECT * FROM messages WHERE ifrom_id = {$uid} ORDER BY id DESC LIMIT 1");
		$a = explode(',', $lastMessage['ai_attach_list']);
		expect(count($a), 1, 'Последнее отправленное has one attach');
		$lastMid = $lastMessage['id'];
		
		$this->attachOne($lastMid);
		
		$lastMessage = dbrow("SELECT * FROM messages WHERE id = {$lastMid}");
		$a = explode(',', $lastMessage['ai_attach_list']);
		expect(count($a), 2, 'Последнее отправленное has two attach');
		
		$data = array(
			'display_user' => $this->recipientNick,
			'subject' => 'Pro',
			'body'    => 'XioXao xdsdsdsd ads '
		);
		$r = $p->submitForm($p->defaultHost . '/a?i=' . $lastMid, 'Отправить', $data);
		
		$lastMessage = dbrow("SELECT * FROM messages WHERE id = {$lastMid}");
		expect($lastMessage['itype'], 1, 'Последнее отправленное has send status');
		$a = explode(',', $lastMessage['ai_attach_list']);
		expect(count($a), 2, 'Последнее отправленное has two attach');
		
	}
	
	private function _send($login, $subject = 'DEfault', $text = 'default', $attach = false) {
		$p = $this->p;
		$data = array(
			'display_user' => $login,
			'subject' => $subject,
			'body'    => $text
		);
		if ($attach) {
			$data['attach'] = $attach;
		}
		$r = $p->submitForm($p->defaultHost . '/a', 'Отправить', $data);
		return $r;
	}
	
	public function login() {
		$p = $this->p;
		$r = $p->submitForm($p->defaultHost, 'Вход', array(
			'login' => $this->phone,
			'password' => $this->password,
			'token' => $this->lastToken
		));
		expectText($r, 'Входящие', 'Логин успешен');
		
	}
	
	public function clearSession() {
		global $p, $req;
		$p->request = null;
		$req = new Request();
		$this->p = $p;
		$this->req = $req;
	}
	public function offRateBan() {
		query("UPDATE users SET last_recipient_id = 0, 	cap_ban_expire = '0000-00-00 00:00:00' WHERE id = {$this->userId}");
	}
}


new TestMessages();
