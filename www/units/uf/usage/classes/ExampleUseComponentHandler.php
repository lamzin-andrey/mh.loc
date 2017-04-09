<?php
require_once APP_ROOT . '/classes/sys/CBaseHandler.php';

require_once APP_ROOT . '/units/uf/php/UFile.php';
class ExampleUseComponentHandler extends CBaseHandler{
	public $uploader;
	public function __construct() {
		$this->css[] = 'simple';
		$this->js[] = 'simple';
		$this->right_inner = 'units/example/uploadfile_example.tpl.php';
		parent::__construct();
		$this->UFile = new UFile($this, 'files', );
	}
}
