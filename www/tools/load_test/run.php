<?php



/** Сколько отправлять запросов к api в секунду */

define('API_PER_SECONDS_LIMIT', 6);

/** Сколько отправлять запросов к сайту в секунду */
define('SITE_PER_SECONDS_LIMIT', 2);

function main() {
	while(true) {
		for ($i = 0; $i < API_PER_SECONDS_LIMIT; $i++) {
			$cmd = '/usr/local/bin/php ' . __DIR__ . '/classes/apiQuery.php ' . $i . ' &';
			exec($cmd);
		}
		for ($i = 0; $i < SITE_PER_SECONDS_LIMIT; $i++) {
			exec('/usr/local/bin/php ' . __DIR__ . '/classes/siteQuery.php ' . $i . ' &');
		}
		sleep(1);
	}
}
main();
