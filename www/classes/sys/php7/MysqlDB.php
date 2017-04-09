<?php
namespace Testtools;

class MysqlDB {
	static private $_host = 'localhost';
	static private $_user;
	static private $_password;
	static private $_dbname;
	static private $_port = 3306;
	
	static public function setHost($host) {
		self::$_host = $host;
	}
	static public function setPassword($password) {
		self::$_password = $password;
	}
	static public function setUser($user) {
		self::$_user = $user;
	}
	static public function setDbname($dbname) {
		self::$_dbname = $dbname;
	}
	static public function setPort($port) {
		self::$_port = $port;
	}
		
	static public function setConnection(&$error, $host = '', $user = '', $password = '', $dbname = '', $port = '') {
		$host      = $host     ? $host     : self::$_host;
		$dbname    = $dbname   ? $dbname   : self::$_dbname;
		$user      = $user     ? $user     : self::$_user;
		$password  = $password ? $password : self::$_password;
		$port      = $port     ? $port     : self::$_port;
		try {
			//TODO set port
			$db = new \PDO('mysql:dbname=' . $dbname . ';host=' . $host . ';port=' . $port, $user, $password);
			return $db;
		}	catch(\PDOException $e) {
			$error =  $e->getMessage();
		}
		return false;
	}
	/**
	 * @param string $cmd sql query || array  [sql query, [$arguments containts values of placeholders] ]
	 * @param int    &$numRows
	 * @return array || int last insert record id
	*/
	static public function query($cmd, &$numRows = 0, &$affectedRows = 0) {
		$arguments = array();
		if (is_array($cmd)) {
			if (isset($cmd[1])) {
				$arguments = $cmd[1];
			}
			$cmd = $cmd[0];
		}
		$pdo = self::setConnection($err);
		$result = array();
		if ($pdo) {
			$lCmd = strtolower($cmd);
			$insert = 0;
			$update = 0;
			if (strpos($lCmd, 'insert') === 0) {
				$insert = 1;
			}
			if (strpos($lCmd, 'update') === 0) {
				$update = 1;
			}
			if (!($insert || $update)) {
				$numRows = 0;
				$sth = $pdo->prepare($cmd);
				$sth->execute($arguments);
				$rawData = $sth->fetchAll();
				//$rawData = $pdo->query($cmd);
				if ($rawData) {
					foreach ($rawData as $row) {
						$rec = array();
						foreach ($row as $k=>$i) {				
							if (strval((int) $k) != strval($k)) {
								$rec[$k] = html_entity_decode($i, ENT_QUOTES);
								$rec[$k] = html_entity_decode($rec[$k], ENT_QUOTES);
							}
						}
						$result[] = $rec;
						$numRows++;
					}
					$pdo = null;
				}
			} else {
				if ($update) {
					$affectedRows = $pdo->exec($cmd);
				} else if ($insert){
					$sequence = null;
					if (isset($arguments[0])) {
						$sequence = $arguments[0];
						array_shift($arguments);
						/*TODO remove me!*/
						//print_r($arguments);
						if (!$sequence) {
							$sequence = null;
						}
					}
					$sth = $pdo->prepare($cmd);
					$sth->execute($arguments);
					/*TODO remove me! echo $cmd . "\n";
					print_r($arguments);*/
					$result = $pdo->lastInsertId($sequence);
					$pdo = null;
					return $result;
				}
			}
		} else {
			if (defined('DEV_MODE')) {
				echo '<div class="bg-rose">';
				var_dump($err);
				echo "\n<hr>\n$cmd<hr>\n";
				echo '</div>';
			}
			$pdo = null;
		}
		return $result;
	}
	/**
	 * @param string $cmd sql query
	 * @param array  $arguments
	 * @param int    &$numRows
	 * @return array || bool false
	*/
	static public function row($cmd, &$numRows = 0) {
		$rows = self::query($cmd, $numRows);
		if ($numRows) {
			return $rows[0];
		}
		return false;
	}
	/**
	 * @param string $cmd sql query
	 * @param array  $arguments
	 * @param int    &$numRows
	 * @return array || bool false
	*/
	static public function val($cmd, &$numRows = 0) {
		$row = self::row($cmd, $numRows);
		if ($numRows && count($row)) {
			return current($row);
		}
		return false;
	}

}
