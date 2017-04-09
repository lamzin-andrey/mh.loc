<?php
require_once dirname (__FILE__) . '/Request.php';
require_once dirname (__FILE__) . '/Parser.php';
require_once dirname (__FILE__) . '/simple_test.php';
global $req, $p;
$req = new Request();
$p = new Parser();
