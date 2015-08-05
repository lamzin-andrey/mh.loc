<?php
require_once APP_ROOT . '/classes/sys/CBaseHandler.php';
require_once APP_ROOT . '/classes/CommentTree.php';
class AcceptCommentHandler extends CBaseHandler{
	protected $_app;
	private $_comment_tree;
	public  $comments_data;
	public function __construct($app) {
		$this->_app = $app;
		$this->left_inner = 'ac_tasklist.tpl.php';
		$this->right_inner = 'ac_inner.tpl.php';
		$this->css[] = 'qs';
		$this->_comment_tree = new CommentTree($app);
		$this->_comment_tree->setUpdateOwnerCondition(null, null);
		$fields = 'comments.title, comments.body, comments.date_create, comments.date_modify, comments.uid, users.name, users.surname, comments.is_accept, comments.is_deleted';
		$join = 'JOIN users ON users.id = comments.uid';
		$this->comments_data = $this->_comment_tree->getRawList("is_accept = 0", $fields, $join);
		parent::__construct();
	}
	
	public function ajaxAction() {
		$action = req('action');
		switch ($action) {
			case 'addComment':
				$this->_addComment();
				break;
			case 'getComment':
				$this->_getComment();
				break;
			case 'acceptComment':
				$this->_acceptComment();
				break;
			case 'removeComment':
				$this->_removeComment();
				break;
		}
	}
	
	private function _addComment() {
		$this->_comment_tree->writeData( array('uid' => CApplication::getUid()) );
		json_ok();
	}
	
	private function _acceptComment() {
		$id = req('id');
		if ($id) {
			query('UPDATE comments SET is_accept = 1 WHERE id = ' . $id);
		}
		json_ok('id', $id);
	}
	
	private function _removeComment() {
		$id = req('id');
		if ($id) {
			query('UPDATE comments SET is_deleted = 1 WHERE id = ' . $id);
		}
		json_ok('id', $id);
	}
	
	private function _getComment() {
		$data = $this->_comment_tree->getRecord(req('id'), 'id');
		if ($data) {
			json_ok_arr($data);
		}
		json_error('msg', $this->_app->lang['Error_record_not_found'] );
	}
	
	
}
