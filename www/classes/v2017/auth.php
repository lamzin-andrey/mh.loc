<?php
require_once __DIR__ . '/mysql.php';
require_once __DIR__ . '/utils.php';

class Auth {
	static public $table = 'apk_users';
	
	
	static public function createUid() {
		$uid = md5(time() . uniqid('dslanhfdkjf', true) . rand(1000,9999) );
		$now = now();
		$table = static::$table;
		$query = "INSERT INTO {$table}
		 (
		 auth_id,
		 date_create
		 )
		 VALUES
		 (
		 '{$uid}',
		 '{$now}'
		 
		 )";
		$id = query($query);
		if ($id) {
			setcookie(AUTH_COOKIE_NAME, $uid, time() + 365 * 24 * 3600, '/');
		}
		return $id;
	}
	
	static public function getUid() {
		$id = 0;
		if (isset($_COOKIE[AUTH_COOKIE_NAME])) {
			$t = self::$table;
			$cname = $_COOKIE[AUTH_COOKIE_NAME];
			$id = (int)dbvalue("SELECT id FROM {$t} WHERE auth_id = '{$cname}'");
		}
		return $id;
	}
	static public function getToken() {
		@session_start();
		$token = sess('auth_token');
		if (!$token) {
			$token = md5(time() . rand(1000, 9999));
			sess('auth_token', $token);
		}
		return $token;
	}
	static public function preparePhone($phone) {
		$phone = trim($phone);
		$plus = 0;
		if ($phone[0] == '+') {
			$plus = 1;
		}
		$s = trim(preg_replace("#[\D]#", "", $phone));
		if ($plus && strlen($s) > 10) {
			$code = substr($s, 0, strlen($s) - 10 );
			$tail = substr($s, strlen($s) - 10 );
			$code++;
			$s = $code . $tail;
		} elseif($plus) {
			$s = '';
		}
		return $s;
	}
	/**
	 * @description Генерирует пароль для пользователя
	 * @return string
	*/
	static public function generatePassword($length = 8) {
		$L = 0;
		$chars = 'abcdefghijklmnopqrstuvwxyz';
		$chars .= strtoupper($chars);
		$chars .= '1234567890';
		$rate = [];//{}
		$str = '';
		$limit = strlen($chars) - 1;
		//randomize();
		while ($L < $length) {
			$ch = $chars[ rand(0, $limit) ];
			if (!isset($rate[$ch]) || $rate[$ch] < 1) {
				$str .= $ch;
				$rate[$ch] = isset($rate[$ch]) ? $rate[$ch] + 1 : 1;
				$L++;
			}
		}
		return $str;
	}
}
