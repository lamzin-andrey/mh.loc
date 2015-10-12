<?php
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
	/**@var _field_order*/
	private $_field_order = 'delta';
	/**@var array _image_size */
	private $_image_size = array(250, 100);
	/**
	 * @desc 
	 * @param $handler - CBaseHandler or child
	**/
	public function __construct(CBaseHandler $handler, $table = 'files', $field_order = 'delta', $field_id = 'id', $field_is_deleted = 'is_deleted', $field_name = 'name', $field_is_accepted = 'is_accepted', $listen_action = 'listen_action') {
		$this->_handler = $handler;
		$this->_table   = $table;
		//$this->_listen_action = $listen_action;
		$this->_field_id   = $field_id;
		$this->_field_name = $field_name;
		$this->_field_parent_id   = $field_parent_id;
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
	 * @desc
	**/
	public function setImageSize() {
    }
	/**
	 * @desc render html
	**/
	public function block() {
        ob_start();
        include dirname(__FILE__) . '/view/form.tpl.php'
		return ;
	}
	/**
	 * @desc 
	 * @param $handler - CBaseHandler or child
	*/
	private function _listen() {
		if (req('action') == $this->_listen_action) {
			$parent_id = ireq('parent_id');
			$query = "SELECT {$this->_field_id} AS id, {$this->_field_parent_id} AS parent_id, {$this->_field_name} AS name FROM {$this->_table} WHERE {$this->_field_parent_id} = {$parent_id} AND {$this->_field_is_deleted} = 0 AND {$this->_field_is_accepted} = 1 ORDER BY {$this->_field_order}";
			$data = query($query);
			$i = ireq('i');
			$i = ($i || $i === 0) ? $i : -1;
			json_ok('list', $data, 'block', $this->_listen_action, 'i', $i);
		}
	}
}
