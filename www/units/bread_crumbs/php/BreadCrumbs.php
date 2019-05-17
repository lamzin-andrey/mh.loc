<?php
if (!defined('WEB_ROOT')) {
	define ('WEB_ROOT', '');
}
/**@desc Должен позволять удобно добавлять на страницу хлебные крошки */
class BreadCrumbs {
	/**@property _handler CBaseHandler child*/
	private $_handler;
	
	/**@property array _data keys is url parts, values is display text*/
	private $_data = [];
	
	/**@property string _prefix the part url before each bread crumb */
	private $_prefix = '';
	
	/**@property string _divider symbol middle each pair of the bread crumbs */
	private $_divider = '»';
	/**
	 * @description 
	 * @param $handler - CBaseHandler or child
	 * @param array $data - массив где ключ - часть ссылки, значение - текст ссылки
	 * @param array $aPrefixes - конкатенацию этого массива надо добавить перед каждой хлебной крошкой
	**/
	public function __construct($handler, array $data, array $aPrefixes) {
		$this->_handler = $handler;
		$this->_data = $data;
		$this->_prefix = preg_replace("#^/+#", '', join('/', $aPrefixes));
		if (!a($handler->components, 'bread_crumbs'))  {
			//$handler->js[] = WEB_ROOT . '/units/bread_crumbs/js/mtscript.js';
			$handler->css[] = WEB_ROOT . '/units/bread_crumbs/css/0.css';
			$handler->components['bread_crumbs'] = 1;
		}
	}
	/**
	 * @description render html Последний элемент массива выводится не как ссылка в том случае, если его ссылка равна REQUEST_URI
	 * @param string $css = 'bc' css селектор блока с хлебными крошками
	 * @param bool $strict = false - если true то для детекта последнего элемента сравниваются ссылки без query_string (символов после первого '?')
	 * @return string html
	**/
	public function block($css = 'bc', $strict = false)
	{
		$req = $_SERVER['REQUEST_URI'];
		if (!$strict) {
			$a = explode('?', $req);
			$req = $a[0];
		}
		if ($req[strlen($req) - 1] != '/') {
			$req .= '/';
		}
		$sBlockHead = '<nav class="' . $css . ' jBreadCrumbs">';
		$sBlockTail = '</nav>';
		if (!$this->_prefix && (!$this->_data || count($this->_data) == 1) ) {
			return ($sBlockHead . $sBlockTail);
		}
		$a = [];
		
		$aPrefix = explode('/', $this->_prefix);
		foreach ($this->_data as $link => $text) {
			$tLink = $link;
			if (!$strict) {
				$b = explode('?', $link);
				$tLink = $b[0];
			}
			if ($tLink != '/') {
				if (!$this->_prefix) {
					$link =  '/' . $link;
					$tLink =  '/' . $tLink;
				} else {
					if (strpos($this->_prefix, $tLink) === false) {
						$tLink =  '/' . $this->_prefix . '/' . $tLink;
						$link =  '/' . $this->_prefix . '/' . $link;
					} else {
						if ($tLink == $aPrefix[0]) {
							$link =  '/' . $link;
							$tLink =  '/' . $tLink . '/';
						} else {
							$link =  '/' . $this->_prefix;
							$tLink =  '/' . $this->_prefix . '/';
						}
					}
				}
			}
			
			if ($req != $tLink) {
				$a[] = '<a href="' . $link . '">' . $text . '</a> ' . $this->_divider . ' ';
			} else {
				$a[] = '<span>' . $text . '</span>';
			}
		}
		return $sBlockHead . join('', $a) . $sBlockTail;
	}
}
