<?php
require_once __DIR__ . '/Query.php';
class SiteQuery extends Query {
	protected $_filesPrefix = 'site_';
}

$n = isset($argv[1]) ? intval($argv[1]) : 0;
new SiteQuery($n);
