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
		$bind_values = '';
		$bind_types = '';
		$by_cols = '';
		foreach ($by_col_name_and_value as $col => $val) {
			$by_cols = $by_cols.','.$col.'= ?';
			list($val,$bind_type) = self::get_value_and_type($val);
			$bind_types = $bind_types.$bind_type;
			$bind_values = $bind_values.','.$val; 
		}
		$bind_values = substr($bind_values, 1);
		$by_cols = substr($by_cols, 1);
		
		$query = 'SELECT '.$get_col_name.' FROM '.$table_name.' WHERE '. $by_cols;
		$sql_stmt = $db->prepare($query);
		if($db->errno > 0){
			throw new SKHR_Exception(self::TAG.$db->error,Messages::PREPARE_QUARY_FAILED);
		}
		$sql_stmt->bind_param($bind_types, $bind_values);
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
	public static function get_row_values($table_name, $by_col_name_and_value, $get_cols_name) 
	{
		$db = DB::get_dblink();
		//Check table existance and more than 0 rows
		//Check columnes existance
// 		echo self::TAG.'pass'."\n";
	
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
			throw new SKHR_Exception(self::TAG.$db->error,Messages::PREPARE_QUARY_FAILED);
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
			throw new SKHR_Exception(self::TAG.$sql_stmt->error ,Messages::PREPARE_QUARY_FAILED);
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
// 		 		echo self::TAG.$query."\n";
		$sql_stmt = $db->prepare($query);
	
		$stmt_bind_param = '$sql_stmt->bind_param(\''.$bind_types.'\','.$bind_params.');';
// 		 		echo self::TAG.$stmt_bind_param. "\n";
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
			echo self::TAG."Updated". $sql_stmt->affected_rows. " rows"."\n";
		}
		$sql_stmt->close();
		
		return($db->affected_rows);
	
	}
	
	public static function remove_row_identify_by_values($table_name, $by_col_name_and_value) {
		$db = DB::get_dblink();
		//Check table existance
		//find Row
		//Add row to archive table/DB
		echo self::TAG.'pass'."\n";
	
	}
	
	
	private function get_value_and_type($val) {
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
			throw new SKHR_Exception(self::TAG.self::$link->error ,Messages::FAILED_TO_CONNECT_DB);
		}
		self::$link->autocommit(FALSE);
	}
	
}

/*----------------- CREATE Tables ------------------*/




?>