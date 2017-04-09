<?php
define("APP_ROOT", dirname(__FILE__));       //чтобы работало повсюду
require_once APP_ROOT . "/config.php";       //config
require_once APP_ROOT . "/CApplication.php"; //logic
$app = new CApplication();
$handler = $app->handler;
$lang = $app->lang;
require_once APP_ROOT . "/" . $app->layout;   //view
