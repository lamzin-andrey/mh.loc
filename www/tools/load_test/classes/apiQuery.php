<?php

require_once __DIR__ . '/Query.php';

class ApiQuery extends Query {
	protected $_filesPrefix = 'api_';
	
	protected function _getQuery() {
		parent::_getQuery();
		if (strpos($this->_query, 'saveWebClientLocation') !== false) {
			$this->_type = static::POST;
			$this->_postData = [
				'stamp' => 'ztGgkSoDziDRFZKLsl5NtcLWwj5CbrMfzUoUG4cT',
				'street' => 'Луконина',
				'home' => '12 к 3',
				'city' => 'Астрахань',
				'lat' => 48.573895,
				'lng' => 39.307697,
				'radius' => 1000,
				'country' => 'Россия'
			];
			$a = explode('|', $this->_query);
			$this->_query = trim($a[0]);
			$this->_postData['stamp'] = trim($a[1]);
		} else {
			$this->_type = static::GET;
			$this->_postData = [];
		}
		file_put_contents($this->_lastRequestFile, $this->_query);
	}
}

$n = isset($argv[1]) ? intval($argv[1]) : 0;
new ApiQuery($n);
