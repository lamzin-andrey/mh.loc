<?
/**
 * Классы реализации поискового дерева
 * **/
class SearchTreeNode {
	public $left = 0;
	public $right = 0;
	public $data;
	public $content;
	public function __construct($n, $content = null) {
		$this->data = $n;
		$this->content = $content;
	}
}

class SearchTree {
	/*
	 * @var указатель на начало (корень) дерева
	**/
	private $_beg;
	/*
	 * @var array массив с отсортированными данными
	**/
	private $_sorted = array();
	
	/**
	 * @param mixed array|int $data 
	**/
	public function __construct($data = null) {
		if ( is_array($data) ) {
			$dataCount = count($data);
			for ($i = 0; $i < $dataCount; $i++) {
				if ($i == 0) {
					$this->first($data[0]);
				} else {
					$this->searchAdd($data[$i]);
				}
			}
		}
	}
	/**
	 * @param int $n 
	**/
	public function first($n, $content = null) {
		$this->_beg = new SearchTreeNode($n, $content);
	}
	/**
	 * @param int $n
	 * @return mixed SearchTreeNode|null $node
	**/
	public function searchAdd($n, $create_new = true, $content = null) {
		$p = $this->_beg;
		$prev;
		while ($p) {
			$prev = $p;
			if ($p->data > $n) {
				$p = $p->left;
			} elseif ($p->data < $n) {
				$p = $p->right;
			} else {
				return $p;
			}
		}
		if ($create_new) {
			$node = new SearchTreeNode($n, $content);
			if ($n > $prev->data) {
				$prev->right = $node;
			} elseif ($n < $prev->data) {
				$prev->left = $node;
			}
			return $node;
		}
		return false;
	}
	/**
	 * @param int $n
	 * @return mixed SearchTreeNode|null $node
	**/
	public function replaceContent($n, $content) {
		$p = $this->_beg;
		$prev;
		while ($p) {
			$prev = $p;
			if ($p->data > $n) {
				$p = $p->left;
			} elseif ($p->data < $n) {
				$p = $p->right;
			} else {
				$p->content = $content; 
				return true;
			}
		}
		return false;
	}
	/**
	 * @desc Обход дерева 
	 * @param SearchTreeNode $node = null
	 * @param int  $level  = 0
	**/
	public function walk($node = null, $level = 0, $conditionObject = null, &$executeObject = null) {
		return $this->sort($node, $level);
	}
	/**
	 * @desc Обход дерева 
	 * @param SearchTreeNode $node = null
	 * @param int  $level  = 0
	 * @param StdClass  $conditionObject содержит имя статического метода и имя класса, который при передаче ему текущей ноды вернет true если надо выполнять действия определенные в $executeObject
	 * @param StdClass  $executeObject содержит имя статического метода и имя класса, который при передаче ему текущей ноды выполнит определенные действия. Результат складывается в  $executeObject->result
	**/
	public function sort($node = null, $level = 0, $conditionObject = null, &$executeObject = null) {
		if ($node === null) {
			$node = $this->_beg;
			$this->_sorted = array();
		}
		if ($node) {
			$this->sort($node->left, $level + 1, $conditionObject, $executeObject);
			$this->_sorted[] = $node->data;
			
			if ($conditionObject && $executeObject) {
				$class = $conditionObject->className;
				$method = $conditionObject->methodName;
				if ($class::$method($node)) {
					if (!isset($executeObject->result)) {
						$executeObject->result = array();
					}
					$class = $executeObject->className;
					$method = $executeObject->methodName;
					$executeObject->result[] = $class::$method($node);
				}
			}
			$this->sort($node->right, $level + 1, $conditionObject, $executeObject);
		}
		return $this->_sorted;
	}
}

