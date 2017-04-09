<?php

require_once dirname(__FILE__) . '/Graphics.php';

$g = new Graphics();

$g->moveTo(1, 1);
$g->lineTo(6, 1);
$g->lineTo(6, 6);
$g->lineTo(1, 6);
$g->lineTo(1, 1);

$o = $g->_objects;
expect(count($o), 5, 'Five points ');
expect($o[0]['is_start'], true, '0 - is start ');
expect($o[1]['is_start'], false, '1 - !is start ');
expect($o[2]['is_start'], false, '2 - !is start ');
expect($o[3]['is_start'], false, '3 - !is start ');
expect($o[4]['is_start'], false, '4 - !is start ');
expect($o[0]['color'], '0x000000', 'Line color');
expect($o[1]['color'], '', '1 Line color');
$g->moveTo(7,8);
$o = $g->_objects;
expect(count($o), 6, 'Six points ');
$g->moveTo(14,20);
expect(count($g->_objects), 6, 'W Six points ');
$o = $g->_objects;
expect($o[5]['x'], 14, 'check x ');
$g->beginFill('0xFF0000');
$g->moveTo(1, 2);
$o = $g->_objects;
expect(count($o), 6, 'tW Six points ');
expect($o[5]['is_begin_fill'], true, 'check bFill ');
expect($o[5]['fill_color'], '0xFF0000', 'check FillColor ');
$g->lineTo(5, 78);
$o = $g->_objects;
expect(count($o), 7, 'Seven points ');
expect($o[6]['is_begin_fill'], false, 'check bFill FALSE');
expect($o[6]['fill_color'], false, 'check FillColor FALSE');
expect($o[6]['is_end_fill'], false, 'check eFill FALSE');
$g->endFill();
$o = $g->_objects;
expect($o[6]['is_end_fill'], true, 'check eFill');
//TODO check thikness
//TODO check rect





function expect($v, $ev, $s = '') {
	if ($v == $ev) {
		print "\033[36m\033[1m{$s} .\033[m\n";
	} else {
		$msg = "{$s} expect {$ev}, got {$v}!";
		echo "\033[41m\033[1m" . $msg . "\033[m\n";
	}
}


