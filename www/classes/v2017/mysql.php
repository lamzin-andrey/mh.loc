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
                        echo '<div class="bg-rose">';
			var_dump($dberror);
			echo "\n<hr>\n$cmd<hr>\n";
                        echo '</div>';
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
					$rec[$k] = html_entity_decode($i, ENT_QUOTES);
					$rec[$k] = html_entity_decode($rec[$k], ENT_QUOTES);
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
    	return db_unsafeString($val);
    }
    mysql_close($link);
    return false;
}
function setConnection() {
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die('Error connect to mysql');
	mysql_select_db(DB_NAME) or die('Error select db ' . DB_NAME);
	mysql_query('SET NAMES UTF8');
	//mysql_query('SET NAMES CP1251');
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
* @desc Заменяет все кавычки в строке на html entity
* @return string $s
**/
function db_safeString(&$s) {
    $s = htmlspecialchars($s, ENT_QUOTES);
    return $s;
}
/**
* @desc Заменяет все кавычки в строке на html entity
* @return string $s
**/
function db_unsafeString(&$s) {
    $s = htmlspecialchars_decode($s, ENT_QUOTES);
    return $s;
}
/**
* @desc Создает INSERT запрос из полей одновременно присутствующих в $data и $tableName
* @param assocArray $data
* @param string $tableName
* @param assocArray $config ключ_в_data => поле_в_tableName
* @param assocArray &$options опции, которые есть в $data, но нет в $tableName
**/
function db_createInsertQuery($data, $tableName, $config = array(), &$options = null) {
    $struct = _db_load_struct_for_table($tableName);
    _db_set_std_values($struct, $data, $tableName);
    $sql_query = 'INSERT INTO {TABLE} 
            ({FIELDS}) 
            VALUES({VALUES});';
    $fields = array();
    $values = array();
    $count = 0;
    $options = array();
    foreach ($data as $key => $item) {
        if (isset($struct[$key]) || (isset($config[$key]) && isset( $struct[ $config[$key] ] ) ) ) {
            if (isset($struct[$key])) {
                $fields[] = '`'. $key .'`';
            } else {
                $fields[] = '`'. $config[$key] .'`';
            }
            $values[] = "'{$item}'";
            $count++;
        } else {
            $options[$key] = $item;
        }
    }
    if ($count) {
        $sql_query = str_replace('{TABLE}', $tableName, $sql_query);
        $sql_query = str_replace('{FIELDS}', join(',', $fields), $sql_query );
        $sql_query = str_replace('{VALUES}', join(',', $values), $sql_query );
        return $sql_query;
    }
    return false;
}
/**
* @desc Создает INSERT запрос из полей одновременно присутствующих в $data и $tableName добавляет в запрос плейсхолдеры {EXT_FIELDS} и {EXT_VALUES} которые позволяют добавлять еще значения
* @param assocArray $data
* @param string $tableName
* @param assocArray $config ключ_в_data => поле_в_tableName
* @param assocArray &$options опции, которые есть в $data, но нет в $tableName
**/
function db_createInsertQueryExt(&$data, $tableName, $config = array(), &$options = null) {
    $struct = _db_load_struct_for_table($tableName);
    _db_set_std_values($struct, $data, $tableName);
    $sql_query = 'INSERT INTO {TABLE} 
            ({FIELDS}{EXT_FIELDS}) 
            VALUES({VALUES}{EXT_VALUES});';
    $fields = array();
    $values = array();
    $count = 0;
    $options = array();
    foreach ($data as $key => $item) {
        if (isset($struct[$key]) || (isset($config[$key]) && isset( $struct[ $config[$key] ] ) ) ) {
            if (isset($struct[$key])) {
                $fields[] = '`'. $key .'`';
            } else {
                $fields[] = '`'. $config[$key] .'`';
            }
            $values[] = "'{$item}'";
            $count++;
        } else {
            $options[$key] = $item;
        }
    }
    if ($count) {
        $sql_query = str_replace('{TABLE}', $tableName, $sql_query);
        $sql_query = str_replace('{FIELDS}', join(',', $fields), $sql_query );
        $sql_query = str_replace('{VALUES}', join(',', $values), $sql_query );
        return $sql_query;
    }
    return false;
}
/**
 * @desc Создает UPDATE запрос из полей одновременно присутствующих в $data и $tableName
 * @param assocArray $data
 * @param string $tableName
 * @param string $condition
 * @param assocArray $config ключ_в_data => поле_в_tableName
 * @param assocArray &$options опции, которые есть в $data, но нет в $tableName
 **/
function db_createUpdateQuery($data, $tableName, $condition, $config = array(), &$options = null) {
    $struct = _db_load_struct_for_table($tableName);
    $sql_query = 'UPDATE {TABLE} SET {PAIRS} WHERE {CONDITION};';
    $pairss = array();
    $count = 0;
    $options = array();
    foreach ($data as $key => $item) {
        if (isset($struct[$key]) || (isset($config[$key]) && isset( $struct[ $config[$key] ] ) ) ) {
            if (isset($struct[$key])) {
                $key = '`'. $key .'`';
            } else {
                $key = '`'. $config[$key] .'`';
            }
            $pairs[] = "{$key} = '{$item}'";
            $count++;
        } else {
            $options[$key] = $item;
        }
    }
    if ($count) {
        $sql_query = str_replace('{TABLE}', $tableName, $sql_query);
        $sql_query = str_replace('{PAIRS}', join(',', $pairs), $sql_query );
        $sql_query = str_replace('{CONDITION}', $condition, $sql_query );
        return $sql_query;
    }
    return false;
}
/**
 * @desc Создает UPDATE запрос из полей одновременно присутствующих в $data и $tableName  добавляет в запрос плейсхолдеры {EXT_PAIRS} который позволяет добавлять еще значения
 * @param assocArray $data
 * @param string $tableName
 * @param string $condition
 * @param assocArray $config ключ_в_data => поле_в_tableName
 * @param assocArray &$options опции, которые есть в $data, но нет в $tableName
 **/
function db_createUpdateQueryExt($data, $tableName, $condition, $config = array(), &$options = null) {
    $struct = _db_load_struct_for_table($tableName);
    $sql_query = 'UPDATE {TABLE} SET {PAIRS} {EXT_PAIRS} WHERE {CONDITION};';
    $pairss = array();
    $count = 0;
    $options = array();
    foreach ($data as $key => $item) {
        if (isset($struct[$key]) || (isset($config[$key]) && isset( $struct[ $config[$key] ] ) ) ) {
            if (isset($struct[$key])) {
                $key = '`'. $key .'`';
            } else {
                $key = '`'. $config[$key] .'`';
            }
            $pairs[] = "{$key} = '{$item}'";
            $count++;
        } else {
            $options[$key] = $item;
        }
    }
    if ($count) {
        $sql_query = str_replace('{TABLE}', $tableName, $sql_query);
        $sql_query = str_replace('{PAIRS}', join(',', $pairs), $sql_query );
        $sql_query = str_replace('{CONDITION}', $condition, $sql_query );
        return $sql_query;
    }
    return false;
}
/**
 * @desc set date_create, delta, uid, user_id if it exists in struct and no set in data
*/
function _db_set_std_values($struct, &$data, $tableName, $exclude = array()) {
    $_ex = array();
    foreach ($exclude as $v) {
        $_ex[$v] = 1;
    }
    $exclude = $_ex;
    if (isset($struct['date_create']) && !isset($data['date_create']) && !isset($exclude['date_create']) ) {
        $data['date_create'] = now();
    }
    if (isset($struct['uid']) && !isset($data['uid']) && !isset($exclude['uid']) ) {
        $data['uid'] = CApplication::getUid();
    }
    if (isset($struct['user_id']) && !isset($data['user_id']) && !isset($exclude['user_id']) ) {
        $data['user_id'] = CApplication::getUid();
    }
    if (defined('DB_DELTA_NOT_USE_TRIGGER') && isset($struct['delta']) && !isset($data['delta']) ) {
        $sql_query = "SELECT MAX(delta) FROM {$tableName}";
        $v = intval(dbvalue($sql_query));
        $data['delta'] = $v + 1;
    }
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
