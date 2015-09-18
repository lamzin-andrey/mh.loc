<?php
class Request {
	/** @const cookie file*/
	const COOKIE_FILE = '/cache/cookie';
	/** @const user agent*/
	const USER_AGENT = 'Mozilla/5.0 (X11; Linux x86_64; rv:38.0) Gecko/20100101 Firefox/38.0';
	/** @var array Массив возможных HTTP статус кодов */
	private $_codes = array(0=>'Domain Not Found',
	               100=>'Continue',
	               101=>'Switching Protocols',
	               200=>'OK',
	               201=>'Created',
	               202=>'Accepted',
	               203=>'Non-Authoritative Information',
	               204=>'No Content',
	               205=>'Reset Content',
	               206=>'Partial Content',
	               300=>'Multiple Choices',
	               301=>'Moved Permanently',
	               302=>'Found',
	               303=>'See Other',
	               304=>'Not Modified',
	               305=>'Use Proxy',
	               307=>'Temporary Redirect',
	               400=>'Bad Request',
	               401=>'Unauthorized',
	               402=>'Payment Required',
	               403=>'Forbidden',
	               404=>'Not Found',
	               405=>'Method Not Allowed',
	               406=>'Not Acceptable',
	               407=>'Proxy Authentication Required',
	               408=>'Request Timeout',
	               409=>'Conflict',
	               410=>'Gone',
	               411=>'Length Required',
	               412=>'Precondition Failed',
	               413=>'Request Entity Too Large',
	               414=>'Request-URI Too Long',
	               415=>'Unsupported Media Type',
	               416=>'Requested Range Not Satisfiable',
	               417=>'Expectation Failed',
	               500=>'Internal Server Error',
	               501=>'Not Implemented',
	               502=>'Bad Gateway',
	               503=>'Service Unavailable',
	               504=>'Gateway Timeout',
	               505=>'HTTP Version Not Supported');
	
	public function __construct($clear_cookie = true) {
		if ($clear_cookie) {
			file_put_contents(dirname(__FILE__) . self::COOKIE_FILE, '');
		}
	}
	/**
	 * @desc  Запрос на сервер
	 * @param string $url
	 * @param array  $args
	 * @param string $referer = ''
	 * @param curl &$process = null
	 * @param bool $close_connection = true можно оставить соединение окрытым
	 * @param is_xhr = false
	 * @param string $userAgent = '' (default see const USER_AGENT)
	 * @return stdClass {responseText, responseStatus. responseStatusText}
	 **/
	public function execute($url, $args = array(), $referer = '', &$process = null, $close_connection = true, $is_xhr = false, $userAgent = '') {
		$this->prepare($url, $args, $referer, $process, $is_xhr, $userAgent);
		$response = curl_exec($process);
		$httpCode = curl_getinfo($process, CURLINFO_HTTP_CODE);
		if ($close_connection) {
			curl_close($process);
		}
		$obj = new StdClass();
		$obj->responseText = $response;
		$obj->responseStatus = $httpCode;
		$obj->responseStatusText = $this->_codes[$httpCode];
		return $obj;
	}
	
	/**
	 * @desc  Подготавливает запрос на сервер, но не отправляет, можно использовать при необходимости использовать multi_curl
	 * @param string $url
	 * @param array  $args
	 * @param string $referer = ''
	 * @param curl_resource  &$process = null
	 * @param is_xhr = false
	 * @param string $userAgent = '' (default see const USER_AGENT)
	 * @return curl_resource $process 
	 **/
	public function prepare($url, $args = array(), $referer = '', &$process = null, $is_xhr = false, $userAgent = '') {
		if (!$process) {
			$process = curl_init($url);
		} else {
			curl_setopt($process, CURLOPT_URL, $url); 
		}
		curl_setopt($process, CURLOPT_HEADER, 0);
		if(count($args) > 0) {
			curl_setopt($process, CURLOPT_POST, 1);
			curl_setopt($process, CURLOPT_POSTFIELDS, $args);
		}
		$headers = array (
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
			'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
			//'Accept-Encoding: gzip, deflate',
			'Content-Type: application/x-www-form-urlencoded'
		);
		if ($is_xhr) {
			$headers[] = 'X-Requested-With: XMLHttpRequest';
		}
		curl_setopt($process, CURLOPT_HTTPHEADER, $headers); 
		
		if ($referer) {
	    	curl_setopt($process, CURLOPT_REFERER, $referer);
		}
		curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
		if (strpos($url, 'https') === 0) {
			curl_setopt($process, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false); 
		}
		curl_setopt($process, CURLOPT_COOKIEFILE, dirname(__FILE__) . self::COOKIE_FILE);
		curl_setopt($process, CURLOPT_COOKIEJAR, dirname(__FILE__) . self::COOKIE_FILE);
		if (!$userAgent) {
			$userAgent = self::USER_AGENT;
		}
		curl_setopt($process, CURLOPT_USERAGENT, $userAgent);
		@curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
		return $process;
	}
	/**
	 * @desc  Запрос на сервер
	 * @param string $url
	 * @param string $data
	 * @param string $referer = ''
	 * @param curl &$process = null
	 * @param bool $close_connection = true можно оставить соединение окрытым
	 * @param string $userAgent = '' (default see const USER_AGENT)
	 * @return stdClass {responseText, responseStatus. responseStatusText}
	 **/
	public function sendRawPost($url, $data, $referer = '', &$process = null, $close_connection = true, $userAgent = '') {
		$this->prepareRawPost($url, $data, $referer, $process, $userAgent);
		$response = curl_exec($process);
		$httpCode = curl_getinfo($process, CURLINFO_HTTP_CODE);
		if ($close_connection) {
			curl_close($process);
		}
		$obj = new StdClass();
		$obj->responseText = $response;
		$obj->responseStatus = $httpCode;
		$obj->responseStatusText = $this->_codes[$httpCode];
		return $obj;
	}
	
	/**
	 * @desc  Подготавливает запрос на сервер, но не отправляет, можно использовать при необходимости использовать multi_curl
	 * @param string $url
	 * @param array  $args
	 * @param string $referer = ''
	 * @param curl_resource  &$process = null
	 * @param is_xhr = false
	 * @param string $userAgent = '' (default see const USER_AGENT)
	 * @return curl_resource $process 
	 **/
	public function prepareRawPost($url, $data, $referer = '', &$process = null, $userAgent = '') {
		if (!$process) {
			$process = curl_init($url);
		} else {
			curl_setopt($process, CURLOPT_URL, $url); 
		}
		curl_setopt($process, CURLOPT_HEADER, 0);
		if(trim($data)) {
			curl_setopt($process, CURLOPT_POST, 1);
			curl_setopt($process, CURLOPT_POSTFIELDS, $data);
		}
		$headers = array (
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
			'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
			'Content-Type: text/plain'
		);
		curl_setopt($process, CURLOPT_HTTPHEADER, $headers); 
		
		if ($referer) {
	    	curl_setopt($process, CURLOPT_REFERER, $referer);
		}
		curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
		if (strpos($url, 'https') === 0) {
			curl_setopt($process, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false); 
		}
		curl_setopt($process, CURLOPT_COOKIEFILE, dirname(__FILE__) . self::COOKIE_FILE);
		curl_setopt($process, CURLOPT_COOKIEJAR, dirname(__FILE__) . self::COOKIE_FILE);
		if (!$userAgent) {
			$userAgent = self::USER_AGENT;
		}
		curl_setopt($process, CURLOPT_USERAGENT, $userAgent);
		@curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
		return $process;
	}
	/**
	 * @desc  multy curl
	 * @param array $resources - ресурсы, инициализированные prepare 
	 * @param array $close_connections - true если надо закрыть соединения
	 * @return array of stdClass {responseText, responseStatus. responseStatusText} 
	 **/
	public function multy($resources, $close_connections = false) {
		$h = curl_multi_init();
		foreach ($resources as $process) {
			curl_multi_add_handle($h, $process);
		}
		$running = null;
		do {
			sleep( 1 );
			curl_multi_exec( $h, $running );
		} while( $running > 0 );
		$results = array();
		foreach ($resources as $process) {
			$obj = new StdClass();
			$obj->responseText = curl_multi_getcontent($process);
			$obj->responseStatus = curl_getinfo($process, CURLINFO_HTTP_CODE);
			$obj->responseStatusText = $this->_codes[$obj->responseStatus];
			if ($close_connections) {
				curl_close($process);
			}
			$results[] = $obj;
		}
		return $results;
	}
	/**
	 * @desc  get cookie as  assoc array
	 * @return assoc array [host_cookie_name => cookie_value, ...]
	*/
	public function getCookie() {
		$file = dirname(__FILE__) . '/cache/cookie';
		if (file_exists($file)) {
			$ls = explode("\n", file_get_contents($file) );
			$cookies = array();
			foreach ($ls as $s) {
				if (strpos($s, "\t") === false) {
					continue;
				}
				$offset = -1;
				$first_tab = 0;
				for ($i = 0; $i < 5; $i++) {
					$offset = strpos($s, "\t", $offset + 1);
					if ($i == 0) {
						$first_tab = $offset;
					}
				}
				$end_name_offset = strpos($s, "\t", $offset + 1);
				$cookie_name = trim(substr($s, $offset + 1, $end_name_offset - $offset));
				$cookie_value = trim(substr($s, $end_name_offset + 1));
				$host_name = substr($s, 0, $first_tab);
				$host_name = str_replace('#HttpOnly_', '', $host_name);
				$host_name = preg_replace("#^\.#", '', $host_name);
				$cookies[ "{$host_name}_{$cookie_name}" ] = $cookie_value;
			}
			return $cookies;
		} else {
			throw new Exception("cookie file not found!");
		}
	}
}
