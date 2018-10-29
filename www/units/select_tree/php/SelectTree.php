<?php
define ('WEB_ROOT', '');
/**@desc Должен позволять удобно добавлять на страницу список иерархических селектов */
class SelectTree {
	/**@var _handler CBaseHandler child*/
	private $_handler;
	/**@var _field_id name*/
	private $_field_id;
	/**@var _table*/
	private $_table;
	/**@var _listen_action - это надо указать в атрибуте data-action*/
	private $_listen_action;
	/**@var _field_parent_id name*/
	private $_field_parent_id;
	/**@var _field_is_deleted name*/
	private $_field_is_deleted;
	/**@var _field_is_accepted*/
	private $_field_is_accepted;
	/**@var _field_name*/
	private $_field_name;
	/**@var _field_order*/
	private $_field_order;
	
	/**@property StdClass _selected_data {selected:array, cats:array} где selected это массив id выбранных категорий, а cats это массив массивов, каждый из которых содержит категории уровня. В 0 элементе все с parent_id  = 0, в 1 элементе все, для которых parent_id  = selected[0], во 2 элементе все, для которых parent_id  = selected[1] и т. д. */
	private $_selected_data;
	/**
	 * @desc 
	 * @param $handler - CBaseHandler or child
	**/
	public function __construct($handler, $table, $listen_action = 'get_childs', $field_order = 'name', $field_id = 'id', $field_parent_id = 'parent_id', $field_is_deleted = 'is_deleted',$field_name = 'name', $field_is_accepted = 'is_accepted') {
		$this->_handler = $handler;
		$this->_table = $table;
		$this->_listen_action = $listen_action;
		$this->_field_id = $field_id;
		$this->_field_name = $field_name;
		$this->_field_parent_id = $field_parent_id;
		$this->_field_is_deleted = $field_is_deleted;
		$this->_field_is_accepted = $field_is_accepted;
		$this->_field_order = $field_order;
		$this->_selected_data = new \StdClass();
		$this->_selected_data->cats = [];
		$this->_selected_data->selected = [];
		if (!a($handler->components, 'select_tree'))  {
			$handler->js[] = WEB_ROOT . '/units/select_tree/js/mtscript.js';
			//$handler->css[] = WEB_ROOT . '/units/select_tree/js/style';
			$handler->components['select_tree'] = 1;
		}
		$this->_listen();
	}
	/**
	 * @desc render html
	*/
	public function block()
	{
		$selectedData = '';
		if (count($this->_selected_data->cats)) {
			$selectedData = '<textarea id="cdata' . $this->_getId() . '" hidden>' . json_encode($this->_selected_data) . '</textarea>';
		}
		if ($this->_listen_action == 'get_childs') {
			return  $selectedData . '<div class="j-select_tree_block"></div>';
		}
		return  $selectedData . '<div class="j-select_tree_block" data-source="'. $this->_getId() .'"></div>';
	}
	/**
	 * @description
	 * @param StdClass {selected:array, cats:array} $data
	*/
	public function setSelectedData($data)
	{
		$this->_selected_data = $data;
	}
	/**
	 * @desc 
	 * @param $handler - CBaseHandler or child
	*/
	private function _listen() {
		if (req('action') == $this->_listen_action) {
			$parent_id = ireq('parent_id');
			$query = "SELECT {$this->_field_id} AS id, {$this->_field_parent_id} AS parent_id, {$this->_field_name} AS name FROM {$this->_table} WHERE {$this->_field_parent_id} = {$parent_id} AND {$this->_field_is_deleted} = 0 AND {$this->_field_is_accepted} = 1 ORDER BY {$this->_field_order}";
			$data = queryc($query);
			$i = ireq('i');
			$i = ($i || $i === 0) ? $i : -1;
			json_ok('list', $data, 'block', $this->_listen_action, 'i', $i);
		}
	}
	/**
	 * @return string
	*/
	private function _getId()
	{
		return preg_replace("#^get_#", '', $this->_listen_action);
	}
}
