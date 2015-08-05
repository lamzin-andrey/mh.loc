<?php
/***
 * Маршрута podat_obyavlenie в тестовом сайте не существует, но тем не менее молжно понять принцип тестирования
*/
class FormAddAdvertTest extends PHPUnit_Framework_TestCase {
	private $_test_user_phone = '89167894512';
	
	private $_default_request = array();
	private $_default_request_copy = array();
	private $_count_prepare_post = 0;
	
	public function __construct() {
		$_SERVER['HTTP_HOST'] = 'bb.loc';
		$_SERVER['REQUEST_URI'] = '/podat_obyavlenie';
		@session_start();
		$_SESSION['capcode'] = '1213231';
		$_SESSION['SERDGHJGHJGDHJSA'] = '121';
		$this->_default_request = array(
			'advcat' => 4,
			'city' => 95,
			'region' => 21,
			'title' => 'Test advert',
			'addtext' => 'Test advert text {N}',
			'price' => '333',
			'name' => 'Testuser',
			'pwd' => 'qW123456',
			'email' => 'test@tuser.amk',
			'phone' => $this->_test_user_phone,
			'token' => $_SESSION['SERDGHJGHJGDHJSA'],
			'cp' => $_SESSION['capcode']
		);
		foreach ($this->_default_request as $k => $i) {
			$this->_default_request_copy[$k] = $i;
		}
		//print_r($this->_default_request);
	}
	/**
	 * Тестирую нормальное добавление объявления
	*/
	public function testAdd() {
		$this->_prepareRequest();
		include './../testindex.php';
		$this->_remove_test_user();
		$this->_prepareRequest();
		include './../testrunapp.php';
		$this->assertTrue($handler->messages[0] == $lang['Success_add_adv']);
		$id = dbvalue("SELECT id FROM users WHERE phone = '{$this->_test_user_phone}'");
		$this->assertTrue(intval($id) > 0);
		//добавление двух объявлений одним и тем же пользователем
		$this->_prepareRequest();
		include './../testrunapp.php';
		$c = dbvalue("SELECT COUNT(id) AS cc FROM prodhash WHERE uid = {$id}");
		$this->assertTrue(intval($c) == 2);
		//добавление третьего объявления без пароля и мыла, с одним только телефоном
		$email = $this->_default_request['email'];
		$pwd = $this->_default_request['pwd'];
		$this->_unsetReq('email', 'pwd');
		$this->_prepareRequest();
		include './../testrunapp.php';
		$this->assertTrue($handler->messages[0] == $lang['Success_add_adv']);
		$this->_restoreReq();
		
		//попытка добавить четвертое объявление - должна быть неуспешной.
		$this->_prepareRequest();
		include './../testrunapp.php';
		$s = $this->_getError($handler, $lang['advert_limit_expired']);
		$this->assertTrue($s == $lang['advert_limit_expired']);
		//print_r($handler->errors);
		//print_r($handler->messages);
		$this->_remove_test_user();
	}
	/**
	 * Тестирую добавление объявления новым пользователем без категории
	*/
	public function testAddWithoutCategory() {
		$this->_unsetReq('advcat');
		$this->_prepareRequest();
		include './../testrunapp.php';
		//print_r($handler->errors);
		//print_r($handler->messages);
		$s = $this->_getError($handler, $lang['Error_category_required']);
		$this->assertTrue($s == $lang['Error_category_required']);
		$this->_restoreReq();
		$this->_remove_test_user();
	}
	/**
	 * Тестирую добавление объявления новым пользователем без региона и без города
	*/
	public function testAddWithoutLocation() {
		$this->_check_without_one_field('region', 'Error_region_required');
	}
	/**
	 * Тестирую добавление объявления новым пользователем без заголовка
	*/
	public function testAddWithoutTitle() {
		$this->_check_without_one_field('title', 'Error_title_required');
	}
	/**
	 * Тестирую добавление объявления новым пользователем с неправильной ценой
	*/
	public function testAddBadPrice() {
		$this->_remove_test_user();
		$this->_default_request['price'] = 'woowoozella';
		$this->_prepareRequest();
		include './../testrunapp.php';

		//print_r($handler->errors);
		//print_r($handler->messages);
		$this->assertTrue($handler->messages[0] == $lang['Success_add_adv']);
		$id = dbvalue("SELECT id FROM users WHERE phone = '{$this->_test_user_phone}'");
		$c = dbvalue("SELECT COUNT(id) AS cc FROM prodhash WHERE uid = {$id}");
		$this->assertTrue($c == 1);
		$price = dbvalue("SELECT price FROM prodhash WHERE uid = {$id}");
		$this->assertTrue($price == 0);
		$this->_restoreReq();
		$this->_remove_test_user();
	}
	/**
	 * Тестирую добавление объявления новым пользователем без заголовка
	*/
	public function testAddWithoutName() {
		$this->_check_without_one_field('name', 'Error_name_required');
	}
	/**
	 * Тестирую добавление объявления новым пользователем без телефона
	*/
	public function testAddWithoutPhone() {
		$this->_check_without_one_field('phone', 'phone_required');
	}
	/**
	 * Тестирую добавление объявления новым пользователем с неправильным телефоном
	*/
	public function testAddBadPhone() {
		$this->_remove_test_user();
		$this->_default_request['phone'] = 'dsafd sfsdf d';
		$this->_prepareRequest();
		include './../testrunapp.php';
		//print_r($handler->errors);
		//print_r($handler->messages);
		$this->assertTrue($handler->errors[0] == $lang['phone_required']);
		$this->_restoreReq();
		$this->_remove_test_user();
	}
	/**
	 * Тестирую добавление объявления новым пользователем с телефоном в различных форматах
	*/
	public function testAddGoodPhone() {
		$this->_check_phone_format('8-916-789-45-12');
		$this->_check_phone_format('+7-916-789-45-12');
		$this->_check_phone_format('+7 (916) 789 45 12');
		$this->_check_phone_format('8 916 789         45-12');
		$s = $this->_test_user_phone;
		$this->_test_user_phone = '21642';
		$this->_check_phone_format('2-16-42');
		$this->_test_user_phone = $s;
	}
	/**
	 * Тестирую добавление объявления новым пользователем пытающимся ввести "плохой" пароль
	*/
	public function testAddBadPassword() {
		$this->_check_bad_password_format('dsafd sfsdf d');
		$this->_check_bad_password_format('easad');
		$this->_check_bad_password_format('Waaaaaaaaaaaaaaaa7');
		$this->_check_bad_password_format('1234567890aw');
		$this->_check_bad_password_format('12345q');
		$this->_check_bad_password_format('aRaRaR');
	}
	/**
	 * Тестирую добавление объявления новым пользователем пытающимся ввести "хороший" пароль
	*/
	public function testAddValidPassword() {
		$this->_check_good_password_format('waswW8');
		$this->_check_good_password_format('1234567890aA');
		$this->_check_good_password_format('a1W123');
	}
	/**
	 * Тестирую добавление объявления новым пользователем без адреса электронной почты
	*/
	public function testAddWithoutEmail() {
		$this->_check_without_one_field('email', 'email_required');
	}
	/**
	 * Тестирую добавление объявления новым пользователем пытающимся ввести "плохой" email
	*/
	public function testAddBadEmail() {
		$this->_check_bad_email_format('asdqwe.ru');
		$this->_check_bad_email_format('Assa@we@.com');
		$this->_check_bad_email_format('a ri@rty.tu');
		$this->_check_bad_email_format('assa@qwe');
		$this->_check_bad_email_format('a+d+e+r@wer.ru');
		$this->_check_bad_email_format('asd@q+e+r*.ru');
	}
	
	
	//========================PRIVATE===================================
	private function _check_bad_email_format($p, $trace = false) {
		$this->_remove_test_user();
		$this->_default_request['email'] = $p;
		$this->_prepareRequest();
		include './../testrunapp.php';
		if ($trace) {
			print_r($handler->errors);
			print_r($handler->messages);
		}
		$this->assertTrue($handler->errors[0] == $lang['email_is_not_valid']);
		$this->_restoreReq();
		$this->_remove_test_user();
	}
	private function _check_good_password_format($p, $trace = false) {
		$this->_remove_test_user();
		$this->_default_request['pwd'] = $p;
		$this->_prepareRequest();
		include './../testrunapp.php';
		if ($trace) {
			print_r($handler->errors);
			print_r($handler->messages);
		}
		$this->assertTrue($handler->messages[0] == $lang['Success_add_adv']);
		$this->_restoreReq();
		$this->_remove_test_user();
	}
	private function _check_bad_password_format($p, $trace = false) {
		$this->_remove_test_user();
		$this->_default_request['pwd'] = $p;
		$this->_prepareRequest();
		include './../testrunapp.php';
		if ($trace) {
			print_r($handler->errors);
			print_r($handler->messages);
		}
		$this->assertTrue($handler->errors[0] == $lang['password_bad_symbols']);
		$this->_restoreReq();
		$this->_remove_test_user();
	}
	private function _check_phone_format($phoneInFormat, $trace = false) {
		$this->_remove_test_user();
		$this->_default_request['phone'] = $phoneInFormat;
		$this->_prepareRequest();
		include './../testrunapp.php';
		if ($trace) {
			print_r($handler->errors);
			print_r($handler->messages);
		}
		$this->assertTrue($handler->messages[0] == $lang['Success_add_adv']);
		$id = dbvalue("SELECT id FROM users WHERE phone = '{$this->_test_user_phone}'");
		$c = dbvalue("SELECT COUNT(id) AS cc FROM prodhash WHERE uid = {$id}");
		$this->assertTrue($c == 1);
		$phone = dbvalue("SELECT phone FROM users WHERE id = {$id}");
		if ($trace) {
			echo "p = '$phone'\n";
		}
		$this->assertTrue($phone == $this->_test_user_phone);
		$this->_restoreReq();
		$this->_remove_test_user();
	}
	
	private function _check_without_one_field($field, $langkey, $trace = false) {
		$this->_unsetReq($field);
		$this->_prepareRequest();
		include './../testrunapp.php';
		if ($trace) {
			print_r($handler->errors);
			print_r($handler->messages);
		}
		$s = $this->_getError($handler, $lang[$langkey]);
		$this->assertTrue($s == $lang[$langkey]);
		$this->_restoreReq();
		$this->_remove_test_user();
	}
	private function _remove_test_user() {
		$_POST = $_REQUEST = array();
		//include './../testindex.php';
		$id = dbvalue("SELECT id FROM users WHERE phone = '{$this->_test_user_phone}'");
		if ($id) {
			query("DELETE FROM prodhash WHERE uid = {$id}");
			query("DELETE FROM users WHERE id = {$id}");
		}
	}
	private function _prepareRequest() {
		$this->_default_request['addtext'] = str_replace('{N}', $this->_count_prepare_post, $this->_default_request['addtext']);
		foreach($this->_default_request as $key => $item) {
			$_POST[$key] = $_REQUEST[$key] = $item;
		}
		$this->_default_request['addtext'] = str_replace($this->_count_prepare_post, '{N}', $this->_default_request['addtext']);
		$this->_count_prepare_post++;
	}
	private function _restoreReq() {
		foreach ($this->_default_request_copy as $k => $i) {
			$this->_default_request[$k] = $i;
		}
	}
	private function _unsetReq() {
		$L = func_num_args();
		for ($i = 0; $i < $L; $i++) {
			$this->_default_request[ func_get_arg($i) ] = '';
		}
	}
	private function _getError($h, $msg) {
		$s = '';
		foreach ($h->errors as $err) {
			if ($err == $msg) {
				$s = $err;
				break;
			}
		}
		return $s;
	}
}
