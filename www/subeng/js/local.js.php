<?
define("APP_ROOT", dirname(__FILE__) . '/..');       //чтобы работало повсюду
require_once APP_ROOT . "/config.php";       //config
require_once APP_ROOT . "/CApplication.php"; //logic
@session_start();
$lang = utils_getCurrentLang();
?>var appLang = <?=json_encode($lang)?>;<?
