<?php
require_once __DIR__ . '/Request.php';
class Query {
	/** Перфикс файлов данных, например api_ или _site */
	protected $_filesPrefix = '';
	
	/** Порядковый номер скрипта */
	protected $_n = 0;
	
	/** Файл с примерами запросов */
	protected $_requestsFile = '';
	
	/** Файл с данными об ответе сервера */
	protected $_logFile = '';
	
	protected $_queries = [];
	
	protected $_queriesSize = 0;
	
	protected $_query = '';
	
	const POST = 2;
	
	const GET = 1;
	
	protected $_type = 1;
	
	protected $_postData = [];
	
	public function __construct($n)
	{
		$this->_n = $n;
		$this->_requestsFile = __DIR__ . '/data/' . $this->_filesPrefix . 'example_requests';
		$this->_lastRequestFile = __DIR__ . '/cache/' . $this->_filesPrefix . '_' . $n . '_last_request';
		if (!file_exists($this->_lastRequestFile)) {
			file_put_contents($this->_lastRequestFile, '');
		}
		$this->_logFile = __DIR__ . '/reports/' . $this->_filesPrefix . '_' . $n . '_log.log';
		if (!file_exists($this->_logFile)) {
			file_put_contents($this->_logFile, '');
		}
		$this->_getQuery();
		$this->_runQuery();
	}
	
	protected function _getQuery() {
		$a = explode("\n", file_get_contents($this->_requestsFile));
		foreach ($a as $line) {
			if (trim($line)) {
				$this->_queries[] = trim($line);
				$this->_queriesSize++;
			}
		}
		shuffle($this->_queries);
		shuffle($this->_queries);
		shuffle($this->_queries);
		$n = rand(0, $this->_queriesSize);
		if ($n == $this->_queriesSize) {
			$n = $this->_queriesSize - 1;
		}
		$this->_query = $this->_queries[$n];
		$this->_type = static::GET;
		file_put_contents($this->_lastRequestFile, $this->_query);
	}
	
	protected function _runQuery() {
		$req = new OuterRequest();
		if ($this->_type == static::GET) {
			$startTime = microtime(true);
			$response = $req->execute($this->_query);
			$endTime = microtime(true);
		} else {
			$startTime = microtime(true);
			$response = $req->sendRawPost($this->_query, json_encode($this->_postData));
			$endTime = microtime(true);
		}
		$tab = "\t";
		$shortUrl = explode('?', $this->_query)[0];
		$a = explode('/', $shortUrl);
		array_shift($a);
		array_shift($a);
		array_shift($a);
		$shortUrl = '/' . join('/', $a);
		$s = '"' . $shortUrl . '"' . $tab .  ($endTime - $startTime) .  $tab . $response->status . $tab . '"' . $this->_query . '"' . "\n";
		file_put_contents($this->_logFile, $s, FILE_APPEND);
	}
}

