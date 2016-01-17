<?php
require_once dirname(__FILE__) . '/Request.php';
class Parser {
	public $defaultHost;
	public $request = null;
	
	public function hasText($html, $text) {
		return (strpos($html, $text) !== false);
	}
	/**
	 * Ограничения: Находит только по input[type=submit], отправляет только поля типа text hidden checkbox
	 * TODO textarea support, radio support
	 * @param string $url путь к странице, с которой надо получить форму
	 * @param string $submitInputLabel Надпись на кнопке submit
	 * @param array  $data параметры для отправки, ключи - имена полей, значения - значения
	 * @param int    $n номер формы в массиве, если их с таким сабмитом на странице несколько
	 * @param string $encode кодировка страницы
	 * @return StdClass $response @see Request:execute +  {error, errorInfo}
	*/
	public function  submitForm($formUrl, $submitInputLabel, $data, $n = 0, $encode = 'UTF-8') {
		if ($this->request === null) {
			$this->request = new Request();
		}
		$req = $this->request;
		$req->urlEncoded = false;
		$response = $req->execute($formUrl);
		
		$s = $response->responseText;
		if ($response->responseStatus != 200) {
			$response->errorInfo = 'Parser::submitForm bad input data';
			$response->error = true;
			return $response;
		}
		$forms = $this->getAllForms($s, $submitInputLabel, $encode);
		if (isset($forms[$n])) {
			$form = $forms[$n];
			$dom = new DOMDocument();
			$dom->validateOnParse = false;
			@$dom->loadHtml($form);
			
			$inputs = $dom->getElementsByTagName('input');
			$fields = array();
			$k = 0;
			for ($i = 0; $i < $inputs->length; $i++) {
				$input = $inputs->item($i);
				if ($input->getAttribute('type') == 'text' || $input->getAttribute('type') == 'hidden' || $input->getAttribute('type') == 'password') {
					$fields[ $input->getAttribute('name') ] = $input->getAttribute('value');
					$k++;
				}
				if ($input->hasAttribute('type') == 'checkbox' ) {
					if ( $input->hasAttribute('checked') ) {
						$fields[ $input->getAttribute('name') ] = 'On';
						$k++;
					}
				}
			}
			if ($k) {
				$domforms = $dom->getElementsByTagName('form');
				if ($domforms->length) {
					$domform = $domforms->item(0);
					if ($domform->hasAttribute('action')) {
						$url = $domform->getAttribute('action');
						if (strpos($url, 'http') !== 0) {
							$url = $this->defaultHost . $url;
						}
						//TODO request method need
						foreach ($fields as $key => $val) {
							if (isset($data[ $key ]) ) {
								$fields[$key] = $data[$key];
							}
						}
						/*echo "{$url}\n : = " . __LINE__ . "\n";
						print_r($fields);*/
						return $req->execute($url, $fields);
					} else {
						$response->errorInfo = 'Parser::submitForm action on concrete form not found';
						$response->error = true;
						return $response;
					}
				} else {
					$response->errorInfo = 'Parser::submitForm concrete form not found';
					$response->error = true;
					return $response;
				}
			} else {
				$response->errorInfo = 'Parser::submitForm fields in concrete form not found';
				$response->error = true;
				return $response;
			}
		}
		$response->errorInfo = 'Parser::submitForm forms not found';
		$response->error = true;
		return $response;
	}
	
	/**
	 * Ограничения: 
	 * @param string $html
	 * @param string $submitInputLabel Надпись на кнопке submit
	 * @param string $encode кодировка страницы
	 * @return array html code of forms on page
	*/
	public function  getAllForms($html, $submitInputLabel, $encode = 'UTF-8') {
		$s = $html;
		if ($encode != 'UTF-8') {
			$s = mb_convert_encoding($s, 'UTF-8', $encode);
		}
		$dom = new DOMDocument();
		$dom->validateOnParse = false;
		@$dom->loadHtml($s);
		$forms = $dom->getElementsByTagName('form');
		$result = array();
		for ($i = 0; $i < $forms->length; $i++) {
			$form = $forms->item($i);
			$submits = $form->getElementsByTagName('input');
			for ($j = 0; $j < $submits->length; $j++) {
				$submit = $submits->item($j);
				if ($submit->getAttribute('value') == $submitInputLabel) {
					$result[] = $dom->saveHtml($form);
				}
			}
		}
		return $result;
	}
	/*
	 * @param string $html
	 * @param string $linkText Содержимое ссылки
	 * @param string $encode кодировка страницы
	 * @return string url
	*/
	public function  getLink($html, $linkText, $encode = 'UTF-8') {
		$s = $html;
		if ($encode != 'UTF-8') {
			$s = mb_convert_encoding($s, 'UTF-8', $encode);
		}
		$dom = new DOMDocument();
		$dom->validateOnParse = false;
		@$dom->loadHtml($s);
		$links = $dom->getElementsByTagName('a');
		for ($i = 0; $i < $links->length; $i++) {
			if ($links->item($i)->textContent == $linkText) {
				if ($links->item($i)->hasAttribute('href')) {
					return $links->item($i)->getAttribute('href');
				}
			}
		}
		return '';
	}
}
