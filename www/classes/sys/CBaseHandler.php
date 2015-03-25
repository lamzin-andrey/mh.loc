<?
class CBaseHandler {
	/** сообщения об ошибках*/
	public $errors = array();
	/** информационные сообщения*/
	public $messages = array();
	/** array массив локализации*/
	public $lang = array();
	/** string шаблон для вывода левой части*/
	public $left_inner;
	/** array специфичные файлы стилей, указывать только имена*/
	public $css;
	/** array специфичные файлы javascript, указывать путь от папки приложения js, например $this->js[] = 'folder/script.js'*/
	public $js;
	/** Объект приложения*/
	protected $_app;
	
	public function __construct($app = null) {
		$this->lang = utils_getCurrentLang();
		if ($app) {
			$this->_app = $app;
		}
	}
	
	public function action404() {
		utils_404();
		$this->left_inner = 'main_tasklist.tpl.php';
		$this->right_inner = 'std/404_promo.tpl.php';
		$this->css[] = 'promo';
	}
	
	
}
