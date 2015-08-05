<?php
//include $_SERVER["DOCUMENT_ROOT"].'/lib/captcha/captcha.php';
include dirname(__FILE__) . '/../../classes/captcha/captcha.php';

function init_cp() {
	@session_start();
	$captcha = new CCaptcha();
	$_SESSION['capcode'] = $captcha->getKeyString();
}
init_cp();
