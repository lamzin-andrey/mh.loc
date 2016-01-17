<?php

function expect($v, $ev, $s = '') {
	if ($v == $ev) {
		print "\033[36m\033[1m{$s} .\033[m\n";
	} else {
		$msg = "{$s} expect {$ev}, got {$v}!";
		echo "\033[41m\033[1m" . $msg . "\033[m\n";
	}
}
