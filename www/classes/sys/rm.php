<?php
function prm($directory) {
	$root = $directory;
	if (file_exists($root) && is_dir($root)) {
		$list = scandir($root);
		foreach ($list as $i) {
			if ($i != '.' && $i != '..') {
				$file = $root . '/' . $i;
				if (is_dir($file)) {
					echo "will unlink recursive {$file}\n";
					prm($file);
				} else {
					echo "will unlink {$file}\n";
					unlink($file);
				}
			}
		}
	}
}


//prm( dirname(__FILE__) . '/lotest' );
