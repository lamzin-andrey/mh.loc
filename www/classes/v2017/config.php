<?php

require_once __DIR__ . '/custom.php';

define('SUMMER_TIME', 3600);
define('APP_CACHE_FOLDER', __dir__ . '/cache');
define('DB_DELTA_NOT_USE_TRIGGER', true);
define('DEV_MODE', true);
define('AUTH_COOKIE_NAME', 'fuid');
define('APP_COOKIE_NAME', 'apid');


define('DOC_ROOT', $_SERVER['DOCUMENT_ROOT']);
define('ROOT', '/f/');



@date_default_timezone_set('Europe/Moscow');
