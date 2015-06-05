<?php
function query($cmd, &$numRows = 0, &$affectedRows = 0) {
	$link = setConnection();
	$lCmd = strtolower($cmd);
	$insert = 0;
	if (strpos($lCmd, 'insert') === 0) {
		$insert = 1;
	}
	global $dberror; 
	global $dbaffectedrows; 
	global $dbnumrows;
	$res = mysql_query($cmd);
	$data = array();
	$dberror = mysql_error();
	if ($dberror) {
		if (defined('DEV_MODE')) {
			var_dump($dberror);
			echo "\n<hr>\n$cmd<hr>\n";
		}
		mysql_close($link);
		return $data;
	}
	
	$numRows = $dbnumrows = @mysql_num_rows($res);
	
	if ($dbnumrows ) {
		while ($row = mysql_fetch_array($res)) {
			$rec =array();
			foreach ($row as $k=>$i) {				
				if (strval((int) $k) != strval($k)) {
					$rec[$k] = htmlspecialchars_decode($i);
				}
			}
			$data[] = $rec;
		}
	}
	$affectedRows = $dbaffectedrows = mysql_affected_rows();
	if ($insert) {
		$id = mysql_insert_id();
		mysql_close($link);
		return $id; 
	}
	mysql_close($link);
	return $data;
}
function dbrow($cmd, &$numRows = null) {
	$link = setConnection();
	$data = query($cmd, $numRows);
	if ($numRows) {
		//mysql_close($link);
		return $data[0];
	}
	//mysql_close($link);
	return array();
}
function dbvalue($cmd) {
	$link = setConnection();
    $res = mysql_query($cmd);
    if (@mysql_num_rows($res) != 0) {
		$val = mysql_result($res, 0, 0);
		mysql_close($link);
    	return $val;
    }
    mysql_close($link);
    return false;
}
function setConnection() {
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die('Error connect to mysql');
	mysql_select_db(DB_NAME) or die('Error select db ' . DB_NAME);
	mysql_query('SET NAMES UTF8');
	return $link;
}
function db_escape(&$s) {
	$s = mysql_escape_string($s);
	return $s;
}
function db_set_delta($id, $table, $delta_field = 'delta', $id_field = 'id') {
	$query = "SELECT MAX({$delta_field}) FROM {$table}";
	$max = (int)dbvalue($query) + 1;
	$query = "UPDATE {$table} SET {$delta_field} = {$max} WHERE {$id_field} = {$id}";
	query($query);
}
/**
* @desc Привести значения полей в POST к типам одноименных полей в таблице $table
* @param string $table
**/
function db_mapPost($table) {
    return _db_map_request($table, $_POST);
}
/**
* @desc Привести значения полей в REQUEST к типам одноименных полей в таблице $table
* @param string $table
**/
function db_mapReq($table) {
    return _db_map_request($table, $_REQUEST);
}
/**
* @desc Привести значения полей в REQUEST к типам одноименных полей в таблице $table
* @param string $table
**/
function db_mapGet($table) {
    return _db_map_request($table, $_GET);
}
/**
* @desc Привести значения полей в data к типам одноименных полей в таблице $table
* @param string $table
**/
function _db_map_request($table, $data = null) {
    $res = array();
    if (!$data) {
        $data = $_REQUEST;
    }
    $struct = _db_load_struct_for_table($table);
    foreach ($data as $field => $value) {
        if ($field_info = a($struct, $field)) {
            switch ($field_info['type']) {
                case 'int':
                case 'bool':
                    $res[$field] = intval($value);
                    if ($field_info['length'] == 1) {
						$res[$field] = $res[$field] ? 1 : 0;
					}
                    break;
                case 'real':
                case 'double':
                    $res[$field] = doubleval($value);
                    break;
                case 'string':
                    $res[$field] = mb_substr($value, 0, $field_info['length'] / 3, 'UTF-8'); //TODO utf8_g_ci
                    $res[$field] = htmlspecialchars($res[$field], ENT_QUOTES);
                    break;
                case 'blob':
                    $res[$field] = htmlspecialchars($value, ENT_QUOTES);
                    break;
                default:
					$res[$field] = htmlspecialchars($value, ENT_QUOTES);
            }
        } else {
            $res[$field] = htmlspecialchars($value, ENT_QUOTES);
        }
    }
    return $res;
}
function _db_load_struct_for_table($table) {
    $file = APP_CACHE_FOLDER . '/' . $table . '.cache';
    if (file_exists( $file ) && DEV_MODE != true) {
        $s = file_get_contents($file);
        $data = json_decode($s, true);
        return $data;
    }
    $link = setConnection();
    $res = mysql_query("SELECT * FROM {$table} LIMIT 1");
    if ( mysql_error() ) {
        echo "Data Source <br>
	    $table
	    <br>
	    was not found
	    <br>
	    Mysql Error:<br>
	    <hr>
	    " . mysql_error()."<hr>";
	    die;
    }
    $data  = array();
    for ($i = 0; $i < mysql_num_fields($res); $i++) {
        $key    = mysql_field_name($res, $i);
        $type   = mysql_field_type($res, $i);
	$len    = mysql_field_len($res, $i);
	$row    = array("type"=>$type, "length"=>$len);
	$data[$key]    = $row;
    }
    mysql_close($link);
    $s = json_encode($data);
    file_put_contents($file, $s);
    return $data;
}
