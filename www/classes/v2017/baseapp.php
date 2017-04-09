<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mysql.php';
require_once __DIR__ . '/utils.php';

class BaseApp {
	/** @proerty string $table */
	public $table;
	
	/** @proerty int $id */
	public $id;
	
	/** @proerty array $request */
	public $request = [];
	
	/** @proerty string $orderDirection */
	public $orderDirection = 'asc';
	
	/** @proerty string $condition fragment of sql WHERE  */
	public $condition = '';
	
	public function req($key, $field = '', $varname = 'REQUEST') {
		$field = $field ? $field : $key;
		$this->$field = req($key, $varname);
		$this->request[$field] = $this->$field;
		return $this->$field;
	}
	
	public function tsreq($key, $field = '', $varname = 'REQUEST') {
		$field = $field ? $field : $key;
		$this->$field = strip_tags( trim(req($key, $varname) ) );
		db_safeString($this->$field);
		$this->request[$field] = $this->$field;
		return $this->$field;
	}
	public function streq($key, $field = '', $varname = 'REQUEST') {
		return $this->tsreq($key, $field = '', $varname);
	}
	
	public function treq($key, $field = '', $varname = 'REQUEST') {
		$field = $field ? $field : $key;
		$this->$field = trim(req($key, $varname) );
		$this->request[$field] = $this->$field;
		return $this->$field;
	}
	
	public function ireq($key, $field = '', $varname = 'REQUEST') {
		$field = $field ? $field : $key;
		$this->$field = ireq($key, $varname);
		$this->request[$field] = $this->$field;
		return $this->$field;
	}
	
	public function breq($key, $field = '', $varname = 'REQUEST') {
		$field = $field ? $field : $key;
		$s = $this->tsreq($key, $field, $varname);
		$this->$field = ($s == 'true' ? true : false);
		return $this->$field;
	}
	
	/**
	 * @see db db_createInsertQuery
	*/
	public function insertQuery($config = [], &$options = null) {
		return db_createInsertQuery($this->request, $this->table, $config, $options);
	}
	
	/**
	 * @see db db_createInsertQueryExt
	*/
	public function insertQueryExt(&$data, $config = [], &$options = null) {
		$data = $this->request;
		return db_createInsertQueryExt($data, $this->table, $config, $options);
	}
	
	/**
	 * @see db db_createUpdateQuery
	*/
	public function updateQuery($condition, $config = [], &$options = null) {
		return db_createUpdateQuery($this->request, $this->table, $condition, $config, $options);
	}
	
	/**
	 * @see db db_createUpdateQueryExt
	*/
	public function updateQueryExt($condition, $config = [], &$options = null) {
		return db_createUpdateQueryExt($this->request, $this->table, $condition, $config, $options);
	}
	
	public function jsonError($messsage) {
		echo json_encode(['error' => $messsage]);
		exit;
	}
	/**
	 * @description Если известны id и table
	*/
	public function rec() {
		$this->id = intval($this->id);
		if ($this->table && $this->id) {
			$row = dbrow("SELECT * FROM {$this->table} WHERE id = {$this->id}");
			foreach ($row as $key => $field) {
				$this->$key = $field;
			}
			return $row;
		}
		return [];
	}
	/**
	 * @description Возвращает страницу запрошенную в request в аргументе $getvarname
	 * Если _GET пуст, то пытается получить номер страницы из /$getvarname/n в основном url
	 * 
	 * Игнорирует is_deleted = 1
	*/
	public function getPage($getvarname = 'page', $perpage = 10) {
		if ($this->table) {
			$page = ireq($getvarname, 'GET');
			if (!$page) {
				$aUrl = explode('/', $_SERVER['REQUEST_URI']);
				if ($s = a($aUrl, count($aUrl) - 2) ) {
					if ($s == $getvarname) {
						$page = intval( a($aUrl, count($aUrl) - 1) );
					}
				}
			}
			if (!$page) {
				$page = 1;
			}
			$offset = ($page - 1) * $perpage;
			$command = "SELECT * FROM {$this->table} WHERE is_deleted != 1 {$this->condition} ORDER BY id {$this->orderDirection} LIMIT {$offset}, {$perpage}";
			//var_dump($command);	die(__file__ . __line__);
			$rows = query($command);
			return $rows;
		}
		return [];
	}
}

