<?php
/**
TODO
 * 1/ Продолжаем аплоад, см TODO
 * 2/ Проверить для nojs
 * 3/ JS НЕ должен при возможности сразу после выбора начинать аплоадить файл.
 *    disable кнопку, показать прелоадер, при окончании раздисаблить, прелоадер скрыть
 *    Кнопка Удалить для файла должна быть
 * 4/Feature Должен быть конфиг (поле класса), при true JS  должен при возможности сразу после выбора начинать аплоадить файл.
 *    и при этом должна быть кнопка "Остановить"
 * false: JS НЕ должен при возможности сразу после выбора начинать аплоадить файл. Должна быть кнопка загрузить.
 */
require_once APP_ROOT . '/classes/sys/CBaseHandler.php';
/**@desc Должен позволять удобно загружать файлы на сервер */
class UFile {
	/**@var _handler CBaseHandler child*/
	private $_handler;
	/**@var _field_id name*/
	private $_field_id = 'id';
	/**@var _table*/
	private $_table = 'files';
	/**@var _listen_action переопределить, если на странице несколько аплоадеров для разных таблиц*/
	private $_listen_action = 'uploadf2';
    /**@var _field_is_deleted name*/
	private $_field_is_deleted = 'is_deleted';
	/**@var _field_is_accepted*/
	private $_field_is_accepted = 'is_accepted';
	/**@var _field_name имя поля, хранящего относительный путь к файлу от каталога files, for example /2015/10/filename.ext */
	private $_field_name = 'name';
    /**@var _field_display_name имя поля, хранящего исходное имя файла */
	private $_field_display_name = 'display_name';
	/**@var _field_order*/
	private $_field_order = 'delta';
	/**@var array _image_size */
	private $_image_size = array(600, 800);
	/**@var array _preview_size */
	private $_preview_size = array(250, 100);
	/**@var string _submit_name */
	private $_submit_name = 'Upload';
	/**
	 * @desc 
	 * @param $handler - CBaseHandler or child
	 * @param string $listen_action идентификатор файла на странице
	 * @param string $table       name of table
	 * @param string $field_order field of table for order
	 * @param string $is_deleted  field of table for deleted flag
	 * @param string $field_name  имя поля, хранящего относительный путь к файлу от каталога files, for example /2015/10/filename.ext
	 * @param string $is_accepted флаг говорит о том, что файл проверен модератором
	**/
	//TODO продумать порядок параметров
	public function __construct(CBaseHandler $handler, $listen_action = 'ufile', $table = 'files', $field_order = 'delta', $field_id = 'id', $field_is_deleted = 'is_deleted', $field_name = 'name', $field_is_accepted = 'is_accepted') {
		$this->_handler = $handler;
		$this->_table   = $table;
		$this->_listen_action = $listen_action;
		$this->_field_id   = $field_id;
		$this->_field_name = $field_name;
		$this->_field_is_deleted  = $field_is_deleted;
		$this->_field_is_accepted = $field_is_accepted;
		$this->_field_order   = $field_order;
		$this->_listen_action = $listen_action;
		if (!a($handler->components, 'uploader_file_2'))  {
            $module = 'uf';
			$handler->js[] = WEB_ROOT . '../units/'. $module .'/js/script.js';
			$handler->css[] = WEB_ROOT . '../units/'. $module .'/js/style';
			$handler->components[$module] = 1;
		}
		$this->_listen();
	}
    /**
	 * @desc Установить размер изображения
	**/
	public function setImageSize($width, $height) {
        $this->_image_size = array($width, $height);
    }
    /**
	 * @desc Установить размер изображения
	**/
	public function setPreviewSize($width, $height) {
        $this->_preview_size = array($width, $height);
    }
	/**
	 * @desc render html
	**/
	public function block() {
        ob_start();
        include dirname(__FILE__) . '/view/form.tpl.php'
		return ob_get_contents();
	}
	/**
	 * @desc
	**/
	public function setSubmitName($name) {
        $this->_submit_name = $name;
	}
	/**
	 * @desc собственно, загрузка
	 * @param $handler - CBaseHandler or child
	 * TODO  здесь остановился
	*/
	private function _listen() {
		if (req('action') == $this->_listen_action) {
            if (isset($_FILES['file-' . $this->_listen_action])) {
                $f = $_FILES['file-' . $this->_listen_action];
                $is_image = null;
                $path = utils_getFilePath(APP_ROOT, $f['tmp_name'], $f['name'], $is_image);
                if ($path) {
                    $success = move_uploaded_file($f['tmp_name'], $path);
                    if ($success) {
						//TODO если изображение - ресайзить и проверить успешность
						//TODO preview_path(); preview_link(); preview_url();
                        //insert file data
                        $short_path = str_replace(APP_ROOT . '/files', '', $path);
                        /*$fields = array(
                            $this->_field_is_deleted   => 0,
                            $this->_field_is_accepted  => 0,
                            $this->_field_name         => $short_path,
                            $this->_field_display_name => $f['name']
                        );*/
						//TODO проверить, как работает вставка
                        $fields = db_mapPost($this->_table);
                        $sql_query = db_createInsertQuery($fields, $this->_table);
                        $id = query($sql_query);
                        db_set_delta($id, $this->_field_order, $this->_field_id);
                    }
                }
            }
			//TODO продумать, что возвращать в том числе и при неудаче
			//json_ok('list', $data, 'block', $this->_listen_action, 'i', $i);
		}
	}
}
