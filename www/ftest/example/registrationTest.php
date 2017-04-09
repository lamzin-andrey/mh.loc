<?php
require_once dirname (__FILE__) . '/tools/index.php';
$p->defaultHost = 'http://64.loc';
$_SERVER['HTTP_HOST'] = $p->defaultHost;
define('APP_ROOT', dirname (__FILE__) . '/../www');

require_once dirname (__FILE__) . '/../www/config.php';
require_once dirname (__FILE__) . '/../www/classes/sys/mysql.php';

function getToken($r) {
	$html = $r->responseText;
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
	return $token;
}

function getCaptchaCode() {
	global $p;
	if ($p->request) {
		$r = $p->request->execute($p->defaultHost);
		$token = getToken($r);
		if (!$token) {
			expect($token != '', true, 'Cpacode: Token not found');
		}
		$p->request->execute($p->defaultHost . '/img/random');
		$url = 'http://64.loc/capcode';
		$r = $p->request->execute($url, array('cc' => 'p51rzg1lx', 'token' => $token));
		return $r->responseText;
	}
	return '';
}
function expectText($r, $text, $label = '') {
	global $req, $p;
	if (!$label) {
		$label = $text;
	}
	expect($p->hasText($r->responseText, $text), true, $label);
}

function agree() {
	global $req, $p;
	if ($req === null) {
		die('Ura');
	}
	//TODO lic 
	$r = $req->execute($p->defaultHost . '/r');
	//echo $r->responseText . "\n\n\n";
	$lic = $p->getLink($r->responseText, 'Я согласен с условиями, перейти к регистрации');
	//echo $lic . "\n\n\n";
	$r = $req->execute($p->defaultHost . $lic, array(), $p->defaultHost . '/agreement');
	//echo $r->responseText . "\n\n\n";
	//END LIC
	$p->request = $req;
	return getToken($r);
}

//Регистрация пользователя

$phone = '89187353620';
$email = 'lamzin50@mail.ru';
function registerUser($token) {
	global $p;
	$phone = '89187353620';
	$email = 'lamzin50@mail.ru';

	$r = $p->submitForm('http://64.loc/r', 'Регистрация', array(
		'uname' => 'Tester800',
		'usname' => 'Tester800',
		'nick' => 'Tester800',
		'rlogin' => $phone,
		'email' => $email,
		'password' => 'Qw123456',
		'agree' => 'On',
		'password_confirm' => 'Qw123456',
		'token' => $token
	));
	return $r;
}


query("DELETE FROM users WHERE phone = '{$phone}'");
$token = agree();
$r = registerUser($token);

expect($p->hasText($r->responseText, 'Введите символы с изображения'), true, 'После отправки данных о регистрации показывается  каптча');

$row = dbrow("SELECT * FROM users WHERE phone = '{$phone}'", $n);
expect($n, 1, 'Record was append');
expect($row['is_active'], 0, 'user no active');
query("UPDATE users SET is_active = 1 WHERE phone = '{$phone}'");
$row = dbrow("SELECT * FROM users WHERE phone = '{$phone}'", $n);
expect($row['is_active'], 1, 'user now active');

//Если юзер активный повторно вводит телефон не зависимо от сессии показывается что юзер уже есть.
$r = registerUser($token);
expect($p->hasText($r->responseText, 'Пользователь с таким номером телефона уже существует'), true, 'Пользователь с таким номером телефона уже существует');


//Восстановление пароля для неактивного пользователя
query("UPDATE users SET is_active = 0 WHERE phone = '{$phone}'");
$row = dbrow("SELECT * FROM users WHERE phone = '{$phone}'", $n);
expect($row['is_active'], 0, 'user no active');
$capcode = getCaptchaCode();
if (!$capcode) {
	expect(0, 1, 'Empty capcode!! ' . $capcode);
}
$r = $p->submitForm('http://64.loc/remind?action=getpwd', 'Восстановить пароль', array(
	'email' => $email,
	'token' => $token,
	'regfstr' => $capcode,
	'action' => 'sendmail'
));

expectText($r, 'Активных пользователей с таким email не найдено', 'Восстановление пароля для неактивного пользователя - Активных пользователей с таким email не найдено');


//Если юзер повторно вводит неактивный телефон и сессия не вышла, его должно сразу кидать на капчу, запись в базе не должна дублироваться
$r = registerUser($token);

expect($p->hasText($r->responseText, 'Введите символы с изображения'), true, 'После отправки данных о регистрации неактивного юзера с активной сессией показывается  каптча');
$rows = query("SELECT id FROM users WHERE phone = '{$phone}'", $n);
expect($n, 1, 'И запись с таким телефоном в базе одна при этом');


//Если юзер повторно вводит неактивный телефон и сессия вышла, его должно сразу кидать на капчу, запись в базе должна дублироваться
$p->request = null;
$req = new Request();
$token = agree();

$r = registerUser($token);
expect($p->hasText($r->responseText, 'Введите символы с изображения'), true, 'После отправки данных о регистрации неактивного юзера с неактивной сессией показывается  каптча');
$rows = query("SELECT id, is_deleted FROM users WHERE phone = '{$phone}' ORDER BY id ASC", $n);
expect($n, 2, 'И записи с таким телефоном в базе две при этом');
expect($rows[0]['is_deleted'], 1, 'Первая из них помечена как удаленная');
expect($rows[1]['is_deleted'], 0, 'Вторая из них не помечена как удаленная');


//print_r($r);
query("DELETE FROM users WHERE phone = '{$phone}'");
