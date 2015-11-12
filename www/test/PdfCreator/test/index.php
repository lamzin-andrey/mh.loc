<?php
require_once dirname(__FILE__) . '/../dss/DssParser.php';

$filename = dirname(__FILE__) . '/u539f344461.dss';
//$filename = dirname(__FILE__) . '/1pattern.dss';
$parser = new DssParser();
$parser->parseData($filename);
