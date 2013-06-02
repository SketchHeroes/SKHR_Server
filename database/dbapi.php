<?php
require_once("constants.php");


class DBAPI 
{
	const TAG = "dbapi.php, DBAPI: ";
	/*----------------- GET Values ------------------*/
	
	//$by_col_name_and_value = array of key,value as column,value
	public static function get_row_value($table_name, $by_col_name_and_value, $get_col_name) 
	{
		$db = DB::get_dblink();
		
		//Check table existance and more than 0 rows
		//Check columnes existance
		
		$bindParam = new BindParam();
		$qArray = array();
		
		$query = 'SELECT '.$get_col_name.' FROM '.$table_name.' WHERE ';
		
		foreach ($by_col_name_and_value as $col => $val) {
			$qArray[] = $col.' = ?';
			list($val,$bind_type) = self::get_value_and_type($val);
			$bindParam->add($bind_type, $val);
		}
		$query .= implode(' AND ', $qArray);
				
		$sql_stmt = $db->prepare($query);
		
		if($db->errno > 0){
			throw new SKHR_Exception(self::TAG.$db->error,ExitCode::PREPARE_QUARY_FAILED);
		}
		
		call_user_func_array(array($sql_stmt, 'bind_param'), $bindParam->get());
		$sql_stmt->execute();
		
		$db->commit();
		
		$sql_stmt->bind_result($returned_get_col_name);
		$res_array = array();
		while ($sql_stmt->fetch()) {
			array_push($res_array, $returned_get_col_name);
		}
		$sql_stmt->free_result();
		return $res_array;	
	}
	
	//$by_col_name_and_value = hash array of key,value as column,value
	//$get_cols_name = array of cols' names
	public static function get_row_values($table_name, $by_col_name_and_value, $get_cols_names) 
	{
		$db = DB::get_dblink();
		$bindParam = new BindParam();
		$qArray = array();
		
		$get_cols_names_list = '';
		$result_params = '';
		foreach($get_cols_names as $col) {
			$get_cols_names_list.=','.$col;
			$result_params .=', $'.$col;
		}
		$get_cols_names_list = substr($get_cols_names_list, 1);
		$result_params = substr($result_params, 1);
		
		$query = 'SELECT '.$get_cols_names_list.' FROM '.$table_name.' WHERE ';
		
		
		foreach ($by_col_name_and_value as $col => $val) {
			$qArray[] = $col.' = ?';
			list($val,$bind_type) = self::get_value_and_type($val);
			$bindParam->add($bind_type, $val);
		}
		$query .= implode(' AND ', $qArray);
		
		$sql_stmt = $db->prepare($query);
		
		if($db->errno > 0){
			throw new SKHR_Exception(self::TAG.$db->error,ExitCode::PREPARE_QUARY_FAILED);
		}
		
		call_user_func_array(array($sql_stmt, 'bind_param'), $bindParam->get());
		$sql_stmt->execute();
		
		$db->commit();
		
		$bind_result_quary = '$sql_stmt->bind_result('.$result_params.');';
		eval($bind_result_quary);
		$res_array = array();
		while ($sql_stmt->fetch()) {
			foreach($get_cols_names as $value) {
				@eval('$v = $'.$value.';');
				$res_array[$value] = $v;
			}
		}
		$sql_stmt->free_result();
		return $res_array;
	
	}
	
	//$by_col_name_and_value = array of key,value as column,value
	public static function get_rows_value($table_name, $by_col_name_and_value, $get_col_name) 
	{
		$db = DB::get_dblink();
		//Check columnes existance
// 		echo self::TAG.'pass'."\n";
	
	}
	
	//$by_col_name_and_value = hash array of key,value as column,value
	//$get_cols_name = array of cols' names
	public static function get_rows_values($table_name, $by_col_name_and_value, $get_cols_name) 
	{
		$db = DB::get_dblink();
		//Check table existance and more than 0 rows
		//Check columnes existance
// 		echo self::TAG.'pass'."\n";
	
	}
	
	public static function is_value_exist_in_column($table_name, $col_name, $value) {
		return false;
	} 
	
	/*----------------- Add/Remove ROWS ------------------*/
	//$col_name_and_value = array of key,value as column,value
	public static function add_row_with_values($table_name, $col_name_and_value) {
		$db = DB::get_dblink();

		$cols ='';
		$values = '';
		$bind_values = '';
		$bind_types = '';
		$i = 0;
		$bind_params = '';
		$values_array = array();
		foreach ($col_name_and_value as $col => $val) {
			$cols .= ','.$col.' ';
			$values .=', ?';
			list($val,$bind_type) = self::get_value_and_type($val);
			$bind_types .= $bind_type;
			$bind_values .= ','.$val;
			$i++;
			$bind_params .= ', $p'.$i;
			array_push($values_array, $val);
		}
		$cols = '('.substr($cols, 1).')';
		$values = '('.substr($values, 2).')';
		$bind_values = substr($bind_values, 1);
		$bind_params = substr($bind_params, 1);
		
// 		INSERT INTO `users` (`user_fullname`, `user_email`, `user_password`) VALUES ('dddd', 'ddd$dd', '3453245');
		$query = 'INSERT INTO '.$table_name.' '.$cols.' VALUES '.$values;
// 		echo self::TAG.$query."\n";
		$sql_stmt = $db->prepare($query);
		if($db->errno > 0){
			throw new SKHR_Exception(self::TAG.$db->error,ExitCode::PREPARE_QUARY_FAILED);
		}
		
		$stmt_bind_param = '$sql_stmt->bind_param(\''.$bind_types.'\','.$bind_params.');';
// 		echo self::TAG.$stmt_bind_param. "\n";
		eval($stmt_bind_param);
		
// 		echo self::TAG.'$bind_values: '.$bind_values. "\n";
// 		echo self::TAG.'$values_array: '.print_r($values_array)."\n";
		$assign_params = 'list('.$bind_params.') = $values_array;';
		eval($assign_params);
// 		echo self::TAG.'$col_name_and_value: '.print_r($col_name_and_value). "\n";
// 		echo self::TAG.'p1:'.$p1."\n".'p2:'.$p2."\n".'p3:'.$p3."\n".'p4:'.$p4."\n";
		
		$sql_stmt->execute();
	
		$db->commit();
// 		echo self::TAG.'Total rows updated: ' . $db->affected_rows. "\n";
		if ($sql_stmt->errno) {
			throw new SKHR_Exception(self::TAG.$sql_stmt->error ,ExitCode::PREPARE_QUARY_FAILED);
		} else {
// 			echo self::TAG."Updated {$sql_stmt->affected_rows} rows"."\n". '*** id: '.$sql_stmt->insert_id. "\n";
		}
		$inserted_id = $sql_stmt->insert_id;
		$sql_stmt->close();
		
		return($inserted_id);
		
	}
	
	public static function update_row_with_values($table_name, $col_name_and_value_update, $col_name_and_value_find) {
		$db = DB::get_dblink();
		
		$i = 0;
		$bind_values = '';
		$bind_types = '';
		$bind_params = '';
		$values_array = array();
		
		$cols_update ='';
		//$values_update = '';
		foreach ($col_name_and_value_update as $col => $val) {
			$cols_update .= ', '.$col.'=?';
			//$values .=', ?';
			list($val,$bind_type) = self::get_value_and_type($val);
			$bind_types .= $bind_type;
			$bind_values .= ','.$val;
			$i++;
			$bind_params .= ', $p'.$i;
			array_push($values_array, $val);
		}
		$cols_update = substr($cols_update, 1);
		
		$cols_find ='';
		foreach ($col_name_and_value_find as $col => $val) {
			$cols_find .= ', '.$col.'=?';
			//$values .=', ?';
			list($val,$bind_type) = self::get_value_and_type($val);
			$bind_types .= $bind_type;
			$bind_values .= ','.$val;
			$i++;
			$bind_params .= ', $p'.$i;
			array_push($values_array, $val);
		}
		$cols_find = substr($cols_find, 1);
		
		
		$bind_values = substr($bind_values, 1);
		$bind_params = substr($bind_params, 1);
	
		// 		UPDATE `users` SET `user_email`='dimaj@ggrrr' WHERE  `user_id`=33;
		$query = 'UPDATE '.$table_name.' SET '.$cols_update.' WHERE '.$cols_find.';';
		 		echo self::TAG.$query."\n";
		$sql_stmt = $db->prepare($query);
	
		$stmt_bind_param = '$sql_stmt->bind_param(\''.$bind_types.'\','.$bind_params.');';
		 		echo self::TAG.$stmt_bind_param. "\n";
		eval($stmt_bind_param);
	
		// 		echo self::TAG.'$bind_values: '.$bind_values. "\n";
// 		 		echo self::TAG.'$values_array: '.print_r($values_array)."\n";
		$assign_params = 'list('.$bind_params.') = $values_array;';
// 		echo self::TAG.'$assign_params: '.$assign_params."\n";
		eval($assign_params);
		// 		echo self::TAG.'$col_name_and_value: '.print_r($col_name_and_value). "\n";
		// 		echo self::TAG.'p1:'.$p1."\n".'p2:'.$p2."\n".'p3:'.$p3."\n".'p4:'.$p4."\n";
	
		$sql_stmt->execute();
	
		$db->commit();
		// 		echo self::TAG.'Total rows updated: ' . $db->affected_rows. "\n";
		
		if ($sql_stmt->errno) {
			echo self::TAG."FAILURE!!! " . $sql_stmt->error. "\n";
		} else {
			echo self::TAG."Updated ". $sql_stmt->affected_rows. " rows"."\n";
		}
		$affected_rows = $sql_stmt->affected_rows;

		$sql_stmt->close();
		
		return($affected_rows);
	
	}
	
	public static function remove_row_identify_by_values($table_name, $by_col_name_and_value) {
		$db = DB::get_dblink();
		
		$bind_types = '';
		$identifiers = '';
		$identifiers_params = '';
		$i = 0;
		foreach ($by_col_name_and_value as $k => $v) {
			list($val,$bind_type) = self::get_value_and_type($v);
			$bind_types .= $bind_type;
			$identifiers .= ', '.$k.' = ?';
			$i++;
			eval('$p'.$i.' = $val;'); 
			$identifiers_params .= ', $p'.$i;
		}
		$identifiers = substr($identifiers, 1);
		$identifiers_params = substr($identifiers_params, 1);
		
		$query = 'DELETE FROM '.$table_name.' WHERE '.$identifiers.' LIMIT 1';
		$sql_stmt = $db->prepare($query);
		if($db->errno > 0){
			throw new SKHR_Exception(self::TAG.$db->error,ExitCode::PREPARE_QUARY_FAILED);
		}
		$stmt_bind_param = '$sql_stmt->bind_param(\''.$bind_types.'\','.$identifiers_params.');';
		eval($stmt_bind_param);
		
		$sql_stmt->execute();
	
		$db->commit();
	
		$sql_stmt->close();
		
		return($db->affected_rows);
	}

	/*----------------- Run MySQL scripts ------------------*/
	public static function run_mysql_script($script) {
		$db = DB::get_dblink();
		echo self::TAG.'pass'."\n";
		
		
	}
	
	
	private static function get_value_and_type($val) {
		$type = 'i';
// 		echo self::TAG.'val: '.$val."\n";
// 		echo self::TAG.'type: '.gettype($val)."\n";
		switch (gettype($val)) {
			case "boolean":
				if ($val) {$val=1;} else {$val=0;}
				$type = 'i';
				break;
			case "integer":
				$type = 'i';
				break;
			case "double":
				$type = 'd';
				break;
			case "string":
// 				$val = '\''.$val.'\'';
				$type = 's';
				break;
			case "array":
				$val = serialize($val);
				$type = 'b';
				break;
			case "object":
				$val = serialize($val);
				$type = 'b';
				break;
			case "NULL":
				$val = '';
				break;
			default:
				break;
		}
		return array($val, $type);
	}

}

class BindParam
{
	private $values = array(), $types = '';

	public function add( $type, &$value ){
		$this->values[] = $value;
		$this->types .= $type;
	}

	public function get(){
		return self::refValues(array_merge(array($this->types), $this->values));
	}
	
	function refValues($arr)
	{
		if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
		{
			$refs = array();
			foreach($arr as $key => $value)
				$refs[$key] = &$arr[$key];
			return $refs;
		}
		return $arr;
	}
}

Class DB {
	
	private static $link = NULL;
	
// 	// Constructor - open DB connection
// 	function __construct() {
// 		parent::__construct();
// 		$this->set_dblink();
// 	}
	
	// Destructor - close DB connection
	function __destruct() {
		$this->link->close();
	}	

	public static function get_dblink() {
		self::set_dblink();
		return self::$link;
	}
	
	private static function set_dblink() {
		if (self::$link != NULL) {
			if (self::$link->ping()) {
				return;
			}
		}
		
		self::$link = new mysqli(DBParams::DB_SERVER, DBParams::DB_USER, DBParams::DB_PASSWORD, DBParams::DB);
		if(self::$link->connect_errno > 0){
			throw new SKHR_Exception(self::TAG.self::$link->error ,ExitCode::FAILED_TO_CONNECT_DB);
		}
		self::$link->autocommit(FALSE);
	}
	
}

/*----------------- CREATE Tables ------------------*/




?>